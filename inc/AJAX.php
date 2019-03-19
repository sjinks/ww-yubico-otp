<?php
namespace WildWolf\YubicoOTP;

final class AJAX
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
		$this->admin_init();
	}

	public function admin_init()
	{
		\add_action('wp_ajax_yotp-check',        [$this, 'wp_ajax_yotp_check']);
		\add_action('wp_ajax_nopriv_yotp-check', [$this, 'wp_ajax_yotp_check']);
		\add_action('wp_ajax_wwyotp_register',   [$this, 'wp_ajax_wwyotp_register']);
		\add_action('wp_ajax_wwyotp_revoke',     [$this, 'wp_ajax_wwyotp_revoke']);
	}

	public function wp_ajax_yotp_check()
	{
		$login = \stripslashes((string)($_POST['l'] ?? ''));
		$res   = WPUtils::preAuth($login);

		\header('Content-Type: application/json; charset=' . \get_bloginfo('charset'));
		\wp_die(\json_encode(['status' => $res]));
	}

	public function wp_ajax_wwyotp_register()
	{
		\header('Content-Type: application/json; charset=' . \get_bloginfo('charset'));

		$name    = $_POST['n'] ?? null;
		$otp     = $_POST['o'] ?? null;
		$nonce   = $_POST['_wpnonce'] ?? null;
		$user_id = \get_current_user_id();

		if (false === \wp_verify_nonce($nonce, 'wwyotp-add_key')) {
			\wp_die(\json_encode(['ok' => false, 'message' => \__('CSRF token does not match. Please reload the page.', 'ww-yubiotp-admin')]));
		}

		if (empty($name) || empty($otp)) {
			\wp_die(\json_encode(['ok' => false, 'message' => \__('Required parameter missing.', 'ww-yubiotp-admin')]));
		}

		$res = OTPUtils::addKey($user_id, $name, $otp);
		if (\is_scalar($res)) {
			$error = Admin::translateErrorCode($res);
			\wp_die(\json_encode(['ok' => false, 'message' => Admin::errorMessage($error)]));
		}

		$table = new KeyTable(['screen' => 'yotp', 'user_id' => $user_id]);
		\ob_start();
		$table->single_row($res);
		$row = \ob_get_clean();

		$result = [
			'ok'      => true,
			'nonce'   => \wp_create_nonce('wwyotp-add_key'),
			'row'     => $row,
			'message' => Admin::successMessage(Admin::MESSAGE_KEY_ADDED),
		];
		\wp_die(\json_encode($result));
	}

	public function wp_ajax_wwyotp_revoke()
	{
		\header('Content-Type: application/json; charset=' . \get_bloginfo('charset'));

		$key   = $_POST['key']      ?? '';
		$nonce = $_POST['_wpnonce'] ?? '';

		if (false === \wp_verify_nonce($nonce, 'revoke-key_' . $key)) {
			\wp_die(\json_encode(['ok' => false, 'message' => \__('CSRF token does not match. Please reload the page.', 'ww-yubiotp-admin')]));
		}

		OTPUtils::removeKey(\get_current_user_id(), $key);
		$result = [
			'ok'      => true,
			'message' => Admin::successMessage(Admin::MESSAGE_KEY_REVOKED),
		];
		\wp_die(\json_encode($result));
	}
}
