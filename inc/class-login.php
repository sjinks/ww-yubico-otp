<?php

namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Utils\Singleton;
use WP_Error;
use WP_User;

final class Login {
	use Singleton;

	private function __construct() {
		load_plugin_textdomain( 'ww-yubiotp-front', false, plugin_basename( dirname( __DIR__ ) ) . '/lang/' );

		add_action( 'login_enqueue_scripts', [ $this, 'login_enqueue_scripts' ] );
		add_action( 'login_form', [ $this, 'login_form' ] );
		add_filter( 'authenticate', [ $this, 'authenticate' ], 999, 2 );
	}

	public function login_enqueue_scripts(): void {
		$suffix = wp_scripts_get_suffix();
		wp_enqueue_script( 'yotp-login', WP_Utils::assets_url( "yotp-login{$suffix}.js" ), [], '2019041601', true );
		wp_localize_script( 'yotp-login', 'yotpSettings', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
	}

	public function login_form(): void {
		WP_Utils::render( 'login' );
	}

	/**
	 * @param null|WP_User|WP_Error $user
	 * @param string $username 
	 * @return null|WP_User|WP_Error
	 */
	public function authenticate( $user, $username ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$u = WP_Utils::get_user_by_login_or_email( $username );
		if ( null !== $u ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$code = sanitize_text_field( (string) ( $_POST['yotp'] ?? '' ) );
			if ( OTP_Utils::enabled_for( $u->ID ) ) {
				if ( ! OTP_Utils::verify_code( $u->ID, $code ) ) {
					return new WP_Error(
						'authentication_failed',
						__( '<strong>ERROR</strong>: The one time password you have entered is incorrect.', 'ww-yubiotp-front' )
					);
				}

				OTP_Utils::update_key_usage( $u->ID, $code );
			}
		}

		return $user;
	}
}
