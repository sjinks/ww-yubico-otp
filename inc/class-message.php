<?php

namespace WildWolf\WordPress\YubicoOTP;

abstract class Message {
	public const MESSAGE_KEY_ADDED   = 1;
	public const MESSAGE_KEY_REVOKED = 2;

	public static function error( int $code ): string {
		switch ( $code ) {
			case OTP_Utils::KEY_EXISTS:
				return __( 'This key already exists.', 'ww-yubiotp-admin' );
			case OTP_Utils::BAD_OTP:
				return __( 'Incorrect one time password.', 'ww-yubiotp-admin' );
			case OTP_Utils::NETWORK_ERROR:
				return __( 'Network error.', 'ww-yubiotp-admin' );
			case OTP_Utils::BAD_RESPONSE:
				return __( 'Bad response received.', 'ww-yubiotp-admin' );
			case OTP_Utils::TAMPERED_RESPONSE:
				return __( 'The response was tampered.', 'ww-yubiotp-admin' );
			case OTP_Utils::REPLAYED_REQUEST:
				return __( 'Replayed request detected.', 'ww-yubiotp-admin' );
			case OTP_Utils::INTERNAL_ERROR:
				return __( 'Internal error.', 'ww-yubiotp-admin' );
			case OTP_Utils::BAD_CLIENT:
				return __( 'Client ID does not exist or is not allowed to verify OTP codes.', 'ww-yubiotp-admin' );
			case OTP_Utils::UNKNOWN_ERROR:
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
}
