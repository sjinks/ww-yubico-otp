<?php

namespace WildWolf\WordPress\YubicoOTP;

use WP_Session_Tokens;

abstract class Session_Utils {
	public static function get_session(): array {
		$mgr     = WP_Session_Tokens::get_instance( get_current_user_id() );
		$token   = wp_get_session_token();
		$session = $mgr->get( $token );

		return is_array( $session ) ? $session : [];
	}

	/**
	 * @psalm-param array<string,string|null> $values 
	 */
	public static function update_session( array $values ): bool {
		$mgr     = WP_Session_Tokens::get_instance( get_current_user_id() );
		$token   = wp_get_session_token();
		$session = $mgr->get( $token );

		if ( ! $session ) {
			return false;
		}

		foreach ( $values as $k => $v ) {
			if ( null === $v ) {
				unset( $session[ $k ] );
			} else {
				$session[ $k ] = $v;
			}
		}

		$mgr->update( $token, $session );
		return true;
	}
}
