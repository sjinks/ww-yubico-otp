<?php
namespace WildWolf\YubicoOTP;

final class Admin
{
	const ERROR_BAD_PARAMETER = 1;
	const ERROR_BAD_OTP       = 2;
	const ERROR_KEY_EXISTS    = 3;
	const ERROR_UNKNOWN       = 4;
	const MESSAGE_KEY_ADDED   = 1;
	const MESSAGE_KEY_REVOKED = 2;

	private $user_settings_hook;

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
		$this->init();
	}

	public function init()
	{
		\load_plugin_textdomain('ww-yubiotp-admin', /** @scrutinizer ignore-type */ false, \plugin_basename(\dirname(__DIR__)) . '/lang/');

		\add_action('admin_init', [$this, 'admin_init']);
		\add_action('admin_menu', [$this, 'admin_menu']);
	}

	private function register_settings()
	{
		\add_settings_section('default', '', '__return_null', 'ww_yubico_otp');
		\add_settings_field(
			'client_id',
			\__('Client ID', 'ww-yubiotp-admin'),
			[WPUtils::class, 'input_field'],
			'ww_yubico_otp',
			'default',
			['label_for' => 'client_id', 'type' => 'number']
		);

		\add_settings_field(
			'secret_key',
			\__('Secret Key', 'ww-yubiotp-admin'),
			[WPUtils::class, 'input_field'],
			'ww_yubico_otp',
			'default',
			[
				'label_for' => 'secret_key',
				'help'      => \__('If left blank, it will be impossible to verify the integrity of server responses', 'ww-yubiotp-admin'),
			]
		);

		\add_settings_field(
			'endpoints',
			\__('API Endpoints', 'ww-yubiotp-admin'),
			[WPUtils::class, 'textarea'],
			'ww_yubico_otp',
			'default',
			[
				'label_for' => 'endpoints',
				'help'      => \__('Leave blank to use YubiCloud servers.', 'ww-yubiotp-admin'),
			]
		);
	}

	public function admin_init()
	{
		$this->register_settings();

		\add_action('admin_enqueue_scripts',        [$this, 'admin_enqueue_scripts']);
		\add_action('admin_post_wwyotp_add_key',    [$this, 'admin_post_wwyotp_add_key']);
		\add_action('admin_post_wwyotp_revoke_key', [$this, 'admin_post_wwyotp_revoke_key']);

		if (WPUtils::doingAJAX()) {
			AJAX::instance();
		}
	}

	public function admin_menu()
	{
		\add_options_page(\__('Yubico OTP', 'ww-yubiotp-admin'), \__('Yubico OTP', 'ww-yubiotp-admin'), 'manage_options', 'ww-yubico-otp', [$this, 'settings_page']);
		$this->user_settings_hook = \add_users_page(\__('Yubico OTP', 'ww-yubiotp-admin'), \__('Yubico OTP', 'ww-yubiotp-admin'), 'read', 'ww-yubico-otp', [$this, 'user_settings_page']);
	}

	public function admin_enqueue_scripts($hook)
	{
		if ($this->user_settings_hook === $hook) {
			$suffix = \wp_scripts_get_suffix();
			\wp_enqueue_script('yotp-user', WPUtils::assetsUrl("yotp-user{$suffix}.js"), [], '2019041600', true);
			\wp_localize_script('yotp-user', 'yotpSettings', [
				'serverError' => \__('There was an error communicating with the server.', 'ww-yubiotp-admin'),
				'revconfirm' => \__('Are you sure you want to revoke this key?', 'ww-yubiotp-admin'),
			]);
		}
	}

	public function settings_page()
	{
		\assert(\current_user_can('manage_options'));
		WPUtils::render('admin-settings');
	}

	public static function errorMessage(int $code) : string
	{
		switch ($code) {
			case self::ERROR_BAD_PARAMETER: return \__('Required parameter missing.', 'ww-yubiotp-admin');
			case self::ERROR_BAD_OTP:       return \__('Incorrect one time password.', 'ww-yubiotp-admin');
			case self::ERROR_KEY_EXISTS:    return \__('This key already exists.', 'ww-yubiotp-admin');
			default:                        return \__('There was an error processing your request.', 'ww-yubiotp-admin');
		}
	}

	public static function successMessage(int $code) : string
	{
		switch ($code) {
			case self::MESSAGE_KEY_ADDED:   return \__('The key has been successfully added.', 'ww-yubiotp-admin');
			case self::MESSAGE_KEY_REVOKED: return \__('The key has been successfully revoked.', 'ww-yubiotp-admin');
			default:                        return \__('Done.', 'ww-yubiotp-admin');
		}
	}

	public function user_settings_page()
	{
		\assert(\current_user_can('read'));

		$session = WPUtils::getSession();
		$params  = [
			'name' => $session['yotp:name'] ?? '',
		];

		if (!empty($_GET['error'])) {
			$params['error'] = self::errorMessage((int)$_GET['error']);
		}
		elseif (!empty($_GET['message'])) {
			$params['message'] = self::successMessage((int)$_GET['message']);
		}

		$v = ['yotp:name' => null];
		WPUtils::updateSession($v);

		WPUtils::render('user-settings', $params);
	}

	public static function translateErrorCode(int $code) : int
	{
		switch ($code) {
			case OTPUtils::KEY_EXISTS: return self::ERROR_KEY_EXISTS;
			case OTPUtils::BAD_OTP:    return self::ERROR_BAD_OTP;
			default:                   return self::ERROR_UNKNOWN;
		}
	}

	public function admin_post_wwyotp_add_key()
	{
		\check_admin_referer('wwyotp-add_key');

		$fields = (array)($_POST['yotp'] ?? []);
		$name   = $fields['name'] ?? null;
		$otp    = $fields['otp']  ?? null;
		$params = ['yotp:name' => $name];

		if (empty($name) || empty($otp)) {
			WPUtils::updateSession($params);
			\wp_redirect(\admin_url('users.php?page=ww-yubico-otp&error=' . self::ERROR_BAD_PARAMETER));
			return;
		}

		$res = OTPUtils::addKey(\get_current_user_id(), $name, \trim($otp));
		if (\is_scalar($res)) {
			WPUtils::updateSession($params);
			$error = self::translateErrorCode($res);
			\wp_redirect(\admin_url('users.php?page=ww-yubico-otp&error=' . $error));
			return;
		}

		\wp_redirect(\admin_url('users.php?page=ww-yubico-otp&message=' . self::MESSAGE_KEY_ADDED));
	}

	public function admin_post_wwyotp_revoke_key()
	{
		$key = $_POST['key'] ?? '';

		\check_admin_referer('revoke-key_' . $key);
		OTPUtils::removeKey(\get_current_user_id(), $key);
		\wp_redirect(\admin_url('users.php?page=ww-yubico-otp&message=' . self::MESSAGE_KEY_REVOKED));
	}
}
