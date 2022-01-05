<?php

namespace WildWolf\WordPress\YubicoOTP;

use Throwable;
use WildWolf\Yubico\OTP;

/**
 * @psalm-type Key = array{name: string, key: string, created: positive-int, last_used: positive-int}
 */
abstract class OTP_Utils {
	public const YOTP_META_KEY = '_ww_yotp';

	public const KEY_EXISTS    = -1;
	public const BAD_OTP       = -2;
	public const UNKNOWN_ERROR = -3;

	public static function enabled_for( int $user_id ) : bool {
		$id   = Settings::instance()->get_client_id();
		$keys = self::keys_for( $user_id );
		return ! empty( $keys ) && ! empty( $id );
	}

	private static function get_verifier(): OTP {
		$settings = Settings::instance();
		/** @psalm-var numeric-string */
		$id  = $settings->get_client_id();
		$key = $settings->get_secret_key();
		$ep  = $settings->get_endpoint();

		$verifier = new OTP( $id, $key );
		if ( $ep ) {
			$verifier->setEndpoint( $ep );
		}

		return $verifier;
	}

	/**
	 * @psalm-param Key[] $keys 
	 */
	private static function find_key( array $keys, string $key ): bool {
		foreach ( $keys as $entry ) {
			if ( ! strcmp( $entry['key'], $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @psalm-return Key[]
	 */
	public static function keys_for( int $user_id ): array {
		/** @var mixed */
		$meta = get_user_meta( $user_id, self::YOTP_META_KEY, true );
		if ( is_array( $meta ) ) {
			/** @psalm-var Key[] */
			$keys = array_filter( $meta, [ __CLASS__, 'validate_key' ] );
			if ( $meta != $keys ) {
				update_user_meta( $user_id, self::YOTP_META_KEY, array_values( $keys ) );
			}

			return $keys;
		}

		return [];
	}

	/**
	 * @psalm-assert-if-true Key $v
	 * @param mixed $v 
	 * @return bool 
	 */
	private static function validate_key( $v ): bool {
		return is_array( $v ) 
			&& isset( $v['name'] ) && is_string( $v['name'] )
			&& isset( $v['key'] ) && is_string( $v['key'] )
			&& isset( $v['created'] ) && is_int( $v['created'] )
			&& isset( $v['last_used'] ) && is_int( $v['last_used'] );
	}

	/**
	 * @psalm-return Key|int
	 */
	public static function add_key( int $user_id, string $name, string $otp ) {
		try {
			$parts  = OTP::parsePasswordOTP( $otp );
			$prefix = $parts['prefix'];
			$keys   = self::keys_for( $user_id );

			if ( self::find_key( $keys, $prefix ) ) {
				return self::KEY_EXISTS;
			}

			$verifier = self::get_verifier();
			if ( ! $verifier->verify( $otp ) ) {
				return self::BAD_OTP;
			}

			$now = time();
			$key = [
				'name'      => $name,
				'key'       => $prefix,
				'created'   => $now,
				'last_used' => $now,
			];

			$keys[] = $key;
			return update_user_meta( $user_id, self::YOTP_META_KEY, $keys ) !== false ? $key : self::UNKNOWN_ERROR;
		} catch ( Throwable $e ) {
			return self::BAD_OTP;
		}
	}

	public static function remove_key( int $user_id, string $key ): void {
		$keys = self::keys_for( $user_id );

		foreach ( $keys as $idx => $entry ) {
			if ( ! strcmp( $entry['key'], $key ) ) {
				unset( $keys[ $idx ] );
			}
		}

		$keys = array_values( $keys );
		update_user_meta( $user_id, self::YOTP_META_KEY, $keys );
	}

	public static function update_key_usage( int $user_id, string $otp ): void {
		$parts  = OTP::parsePasswordOTP( $otp );
		$prefix = $parts['prefix'];
		$keys   = self::keys_for( $user_id );
		foreach ( $keys as &$entry ) {
			if ( ! strcmp( $entry['key'], $prefix ) ) {
				$entry['last_used'] = time();
				update_user_meta( $user_id, self::YOTP_META_KEY, $keys );
				break;
			}
		}

		unset( $entry );
	}

	public static function verify_code( int $user_id, string $otp ): bool {
		try {
			$parts  = OTP::parsePasswordOTP( $otp );
			$prefix = $parts['prefix'];
			$keys   = self::keys_for( $user_id );

			if ( ! self::find_key( $keys, $prefix ) ) {
				return false;
			}

			return self::get_verifier()->verify( $otp );
		} catch ( Throwable $e ) {
			return false;
		}
	}
}
