<?php

namespace WildWolf\WordPress\YubicoOTP;

abstract class Message {
	public const ERROR_BAD_PARAMETER = 1;
	public const ERROR_BAD_OTP       = 2;
	public const ERROR_KEY_EXISTS    = 3;
	public const ERROR_UNKNOWN       = 4;
	public const MESSAGE_KEY_ADDED   = 1;
	public const MESSAGE_KEY_REVOKED = 2;

	public static function error( int $code ): string {
		switch ( $code ) {
			case self::ERROR_BAD_PARAMETER:
				return __( 'Required parameter missing.', 'ww-yubiotp-admin' );
			case self::ERROR_BAD_OTP:
				return __( 'Incorrect one time password.', 'ww-yubiotp-admin' );
			case self::ERROR_KEY_EXISTS:
				return __( 'This key already exists.', 'ww-yubiotp-admin' );
			default:
				return __( 'There was an error processing your request.', 'ww-yubiotp-admin' );
		}
	}

	public static function success( int $code ): string {
		switch ( $code ) {
			case self::MESSAGE_KEY_ADDED:
				return __( 'The key has been successfully added.', 'ww-yubiotp-admin' );
			case self::MESSAGE_KEY_REVOKED:
				return __( 'The key has been successfully revoked.', 'ww-yubiotp-admin' );
			default:
				return __( 'Done.', 'ww-yubiotp-admin' );
		}
	}

	public static function translate_otp_error_code( int $code ): int {
		switch ( $code ) {
			case OTP_Utils::KEY_EXISTS:
				return self::ERROR_KEY_EXISTS;
			case OTP_Utils::BAD_OTP:
				return self::ERROR_BAD_OTP;
			default:
				return self::ERROR_UNKNOWN;
		}
	}
}
