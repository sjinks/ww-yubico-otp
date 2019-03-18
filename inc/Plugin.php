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
		\register_setting('ww_yubico_otp', self::OPTIONS_KEY, ['default' => []]);

		\add_action('login_init', [Login::class, 'instance']);

		if (\is_admin()) {
			Admin::instance();
		}
	}
}
