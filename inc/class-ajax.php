<?php
namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Utils\Singleton;

final class AJAX {
	use Singleton;

	private function __construct() {
		$this->admin_init();
	}

	public function admin_init(): void {
		add_action( 'wp_ajax_yotp-check', [ $this, 'wp_ajax_yotp_check' ] );
		add_action( 'wp_ajax_nopriv_yotp-check', [ $this, 'wp_ajax_yotp_check' ] );
		add_action( 'wp_ajax_wwyotp_register', [ $this, 'wp_ajax_wwyotp_register' ] );
		add_action( 'wp_ajax_wwyotp_revoke', [ $this, 'wp_ajax_wwyotp_revoke' ] );
	}

	public function wp_ajax_yotp_check(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$login = sanitize_text_field( WP_Utils::get_post_var_as_string( 'l' ) );
		$user  = WP_Utils::get_user_by_login_or_email( $login );

		if ( null !== $user ) {
			$res = OTP_Utils::enabled_for( $user->ID );
		} else {
			// If this user does not exist, show the OTP field anyway
			$res = true;
		}

		wp_send_json( [ 'status' => $res ] );
	}

	public function wp_ajax_wwyotp_register(): void {
		$name  = sanitize_text_field( WP_Utils::get_post_var_as_string( 'n' ) );
		$otp   = sanitize_text_field( WP_Utils::get_post_var_as_string( 'o' ) );
		$nonce = sanitize_text_field( WP_Utils::get_post_var_as_string( '_wpnonce' ) );

		$user_id = get_current_user_id();

		self::verify_nonce( $nonce, 'wwyotp-add_key' );

		if ( empty( $name ) || empty( $otp ) ) {
			wp_send_json( [
				'ok'      => false,
				'message' => __( 'Required parameter missing.', 'ww-yubiotp-admin' ),
			], 400 );
		}

		$res = OTP_Utils::add_key( $user_id, $name, $otp );
		if ( is_scalar( $res ) ) {
			wp_send_json( [
				'ok'      => false,
				'message' => Message::error( $res ),
			], 400 );
		}

		$table = new Key_Table( [
			'screen'  => 'yotp',
			'user_id' => $user_id,
		] );

		ob_start();
		$table->single_row( $res );
		$row = ob_get_clean();

		$result = [
			'ok'      => true,
			'nonce'   => wp_create_nonce( 'wwyotp-add_key' ),
			'row'     => $row,
			'message' => Message::success( Message::MESSAGE_KEY_ADDED ),
		];

		wp_send_json( $result );
	}

	public function wp_ajax_wwyotp_revoke(): void {
		$key   = sanitize_text_field( WP_Utils::get_post_var_as_string( 'key' ) );
		$nonce = sanitize_text_field( WP_Utils::get_post_var_as_string( '_wpnonce' ) );

		self::verify_nonce( $nonce, 'revoke-key_' . $key );

		OTP_Utils::remove_key( get_current_user_id(), $key );
		$result = [
			'ok'      => true,
			'message' => Message::success( Message::MESSAGE_KEY_REVOKED ),
		];
		wp_send_json( $result );
	}

	private static function verify_nonce( string $nonce, string $action ): void {
		if ( false === wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json( [
				'ok'      => false,
				'message' => __( 'CSRF token does not match. Please reload the page.', 'ww-yubiotp-admin' ),
			], 400 );
		}
	}
}
