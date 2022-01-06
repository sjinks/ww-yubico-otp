<?php

namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Utils\Singleton;
use WildWolf\WordPress\WP_Request_Context;

final class Admin {
	use Singleton;

	public const OPTIONS_MENU_SLUG      = 'ww-yubico-otp';
	public const USER_OPTIONS_MENU_SLUG = 'ww-yubico-otp-user';

	/** @var string|false */
	private $user_settings_hook = false;

	private function __construct() {
		$this->init();
	}

	public function init(): void {
		load_plugin_textdomain( 'ww-yubiotp-admin', false, plugin_basename( dirname( __DIR__ ) ) . '/lang/' );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_init', [ AdminSettings::class, 'instance' ] );

		if ( WP_Request_Context::is_ajax() ) {
			add_action( 'admin_init', [ AJAX::class, 'instance' ] );
		}
	}

	public function admin_init(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	public function admin_menu(): void {
		add_options_page( __( 'Yubico OTP', 'ww-yubiotp-admin' ), __( 'Yubico OTP', 'ww-yubiotp-admin' ), 'manage_options', self::OPTIONS_MENU_SLUG, [ $this, 'settings_page' ] );
		$this->user_settings_hook = add_users_page( __( 'Yubico OTP', 'ww-yubiotp-admin' ), __( 'Yubico OTP', 'ww-yubiotp-admin' ), 'read', self::USER_OPTIONS_MENU_SLUG, [ $this, 'user_settings_page' ] );
	}

	/**
	 * @param string $hook 
	 */
	public function admin_enqueue_scripts( $hook ): void {
		if ( $this->user_settings_hook === $hook ) {
			$suffix = wp_scripts_get_suffix();
			wp_enqueue_script( 'yotp-user', WP_Utils::assets_url( "yotp-user{$suffix}.js" ), [], '2019041601', true );
			wp_localize_script('yotp-user', 'yotpSettings', [
				'serverError' => __( 'There was an error communicating with the server.', 'ww-yubiotp-admin' ),
				'revconfirm'  => __( 'Are you sure you want to revoke this key?', 'ww-yubiotp-admin' ),
			] );
		}
	}

	public function settings_page(): void {
		assert( current_user_can( 'manage_options' ) );
		WP_Utils::render( 'admin-settings' );
	}

	public function user_settings_page(): void {
		assert( current_user_can( 'read' ) );
		WP_Utils::render( 'user-settings' );
	}
}
