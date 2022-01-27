<?php

namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Yubico\OTP\Transport;
use WildWolf\Yubico\OTPTransportException;

class WP_Transport extends Transport {
	protected function sendRequest( string $endpoint ): string {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		$response = wp_remote_get( $endpoint );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			throw new OTPTransportException();
		}

		return wp_remote_retrieve_body( $response );
	}
}
