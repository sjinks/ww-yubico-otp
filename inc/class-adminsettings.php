<?php

namespace WildWolf\WordPress\YubicoOTP;

use WildWolf\Utils\Singleton;

final class AdminSettings {
	use Singleton;

	const OPTION_GROUP = 'ww_yubico_otp';

	private InputFactory $input_factory;

	/**
	 * Constructed during `admin_init`
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->register_settings();
	}

	public function register_settings(): void {
		$this->input_factory = new InputFactory( Settings::OPTION_KEY, Settings::instance() );
		register_setting(
			self::OPTION_GROUP,
			Settings::OPTION_KEY,
			[
				'default'           => Settings::defaults(),
				'sanitize_callback' => [ SettingsValidator::class, 'sanitize' ],
			]
		);

		$settings_section = 'default';
		add_settings_section( $settings_section, '', '__return_null', 'ww_yubico_otp' );
		add_settings_field(
			'client_id',
			__( 'Client ID', 'ww-yubiotp-admin' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'client_id',
				'type'      => 'number',
			]
		);

		add_settings_field(
			'secret_key',
			__( 'Secret Key', 'ww-yubiotp-admin' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'secret_key',
				'help'      => __( 'If left blank, it will be impossible to verify the integrity of server responses', 'ww-yubiotp-admin' ),
			]
		);

		add_settings_field(
			'endpoints',
			__( 'API Endpoints', 'ww-yubiotp-admin' ),
			[ $this->input_factory, 'textarea' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'rows'      => 5,
				'cols'      => 40,
				'label_for' => 'endpoints',
				'help'      => __( 'Leave blank to use YubiCloud servers.', 'ww-yubiotp-admin' ),
			]
		);
	}
}
