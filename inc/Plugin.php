<?php
namespace WildWolf\YubicoOTP;

final class Plugin
{
	const OPTIONS_KEY = 'ww_yubico_otp';

	public static function instance()
	{
		static $self = null;

		if (!$self) {
			$self = new self();
		}

		return $self;
	}

	private function __construct()
	{
		\add_action('init', [$this, 'init']);
	}

	public function init()
	{
		\load_plugin_textdomain('ww-yubiotp-front', /** @scrutinizer ignore-type */ false, \plugin_basename(\dirname(__DIR__)) . '/lang/');
		\register_setting('ww_yubico_otp', self::OPTIONS_KEY, ['default' => []]);

		\add_action('login_form_login', [Login::class, 'instance']);

		if (\is_admin()) {
			Admin::instance();
		}
	}
}
