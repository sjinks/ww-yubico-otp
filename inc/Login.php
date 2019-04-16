<?php
namespace WildWolf\YubicoOTP;

final class Login
{
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
		$this->login_init();
	}

	public function login_init()
	{
		\load_plugin_textdomain('ww-yubiotp-front', /** @scrutinizer ignore-type */ false, \plugin_basename(\dirname(__DIR__)) . '/lang/');

		\add_action('login_enqueue_scripts', [$this, 'login_enqueue_scripts']);
		\add_action('login_form',            [$this, 'login_form']);
		\add_filter('authenticate',          [$this, 'authenticate'], 999, 3);
	}

	public function login_enqueue_scripts()
	{
		$suffix = \wp_scripts_get_suffix();
		\wp_enqueue_script('yotp-login', WPUtils::assetsUrl("yotp-login{$suffix}.js"), [], '2019041601', true);
		\wp_localize_script('yotp-login', 'yotpSettings', ['ajaxurl' => \admin_url('admin-ajax.php')]);
	}

	public function login_form()
	{
		WPUtils::render('login');
	}

	public function authenticate($user, $username, /** @scrutinizer ignore-unused */ $password)
	{
		if (\is_wp_error($user)) {
			return $user;
		}

		$u = WPUtils::getUserByLoginOrEmail($username);
		if (false !== $u) {
			$code = \trim($_POST['yotp'] ?? '');
			if (OTPUtils::enabledFor($user->ID)) {
				if (!OTPUtils::verifyCode($user->ID, $code)) {
					return new \WP_Error('authentication_failed', \__('<strong>ERROR</strong>: The one time password you have entered is incorrect.', 'ww-yubiotp-front'));
				}

				OTPUtils::updateKeyUsage($user->ID, $code);
			}
		}

		return $user;
	}
}
