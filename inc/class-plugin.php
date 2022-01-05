<?php

namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Utils\Singleton;

final class Plugin {
	const OPTIONS_KEY = 'ww_yubico_otp';

	use Singleton;

	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init(): void {
		load_plugin_textdomain( 'ww-yubiotp-front', false, plugin_basename( dirname( __DIR__ ) ) . '/lang/' );
		register_setting( 'ww_yubico_otp', self::OPTIONS_KEY, [ 'default' => [] ] );

		add_action( 'login_form_login', [ Login::class, 'instance' ] );

		if ( is_admin() ) {
			Admin::instance();
		}
	}
}
