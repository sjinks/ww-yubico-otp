<?php
namespace WildWolf\YubicoOTP;

use WildWolf\Yubico\OTP;

abstract class OTPUtils
{
	const YOTP_META_KEY = '_ww_yotp';

	const KEY_EXISTS    = -1;
	const BAD_OTP       = -2;
	const UNKNOWN_ERROR = -3;

	public static function enabledFor(int $user_id) : bool
	{
		$options = \get_option(Plugin::OPTIONS_KEY, []);
		$id      = $options['client_id']  ?? null;
		$keys    = self::keysFor($user_id);
		return !empty($keys) && !empty($id);
	}

	private static function getVerifier() : OTP
	{
		$options = \get_option(Plugin::OPTIONS_KEY, []);
		$id      = $options['client_id']  ?? '';
		$key     = $options['secret_key'] ?? '';
		$ep      = $options['endpoints']  ?? null;

		$verifier = new OTP($id, $key);
		if ($ep) {
			$ep = \array_filter(\array_map('\\trim', \explode("\n", $ep)));
			$verifier->setEndpoints($ep);
		}

		return $verifier;
	}

	private static function findKey(array $keys, string $key) : bool
	{
		foreach ($keys as $entry) {
			if (!\strcmp($entry['key'], $key)) {
				return true;
			}
		}

		return false;
	}

	public static function keysFor(int $user_id) : array
	{
		$meta = \get_user_meta($user_id, self::YOTP_META_KEY, true);
		return \is_array($meta) ? $meta : [];
	}

	public static function addKey(int $user_id, string $name, string $otp)
	{
		try {
			$parts  = OTP::parsePasswordOTP($otp);
			$prefix = $parts['prefix'];
			$keys   = \get_user_meta($user_id, self::YOTP_META_KEY, true);

			if (!\is_array($keys)) {
				$keys = [];
			}

			if (self::findKey($keys, $prefix)) {
				return self::KEY_EXISTS;
			}

			$verifier = self::getVerifier();
			if (!$verifier->verify($otp)) {
				return self::BAD_OTP;
			}

			$now    = \time();
			$key    = ['name' => $name, 'key' => $prefix, 'created' => $now, 'last_used' => $now];
			$keys[] = $key;
			return \update_user_meta($user_id, self::YOTP_META_KEY, $keys) !== false ? $key : self::UNKNOWN_ERROR;
		}
		catch (\Throwable $e) {
			return self::BAD_OTP;
		}
	}

	public static function removeKey(int $user_id, string $key)
	{
		$keys = \get_user_meta($user_id, self::YOTP_META_KEY, true);

		if (!\is_array($keys)) {
			return;
		}

		foreach ($keys as $idx => $entry) {
			if (!\strcmp($entry['key'], $key)) {
				unset($keys[$idx]);
			}
		}

		$keys = \array_values($keys);
		return \update_user_meta($user_id, self::YOTP_META_KEY, $keys);
	}

	public static function updateKeyUsage(int $user_id, string $otp)
	{
		$parts  = OTP::parsePasswordOTP($otp);
		$prefix = $parts['prefix'];
		$keys   = \get_user_meta($user_id, self::YOTP_META_KEY, true);
		foreach ($keys as &$entry) {
			if (!\strcmp($entry['key'], $prefix)) {
				$entry['last_used'] = \time();
				\update_user_meta($user_id, self::YOTP_META_KEY, $keys);
				break;
			}
		}

		unset($entry);
	}

	public static function verifyCode(int $user_id, string $otp) : bool
	{
		try {
			$parts  = OTP::parsePasswordOTP($otp);
			$prefix = $parts['prefix'];
			$keys   = \get_user_meta($user_id, self::YOTP_META_KEY, true);

			if (!\is_array($keys)) {
				$keys = [];
			}

			if (!self::findKey($keys, $prefix)) {
				return false;
			}

			$verifier = self::getVerifier();
			return $verifier->verify($otp);
		}
		catch (\Throwable $e) {
			return false;
		}
	}
}
