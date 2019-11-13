<?php
namespace WildWolf\YubicoOTP;

abstract class WPUtils
{
	public static function render(string $view, array $params = [])
	{
		require __DIR__ . '/../views/' . $view . '.php';
	}

	public static function assetsUrl(string $file) : string
	{
		return \plugins_url('assets/' . $file, \dirname(__DIR__) . '/plugin.php');
	}

	public static function doingAJAX() : bool
	{
		return \defined('\\DOING_AJAX') && \DOING_AJAX;
	}

	/**
	 * @param string $s
	 * @return \WP_User|false
	 */
	public static function getUserByLoginOrEmail(string $s)
	{
		$user = \get_user_by('login', $s);
		if (!$user && \strpos($s, '@')) {
			$user = \get_user_by('email', $s);
		}

		return $user;
	}

	public static function preAuth(string $login) : bool
	{
		$user = self::getUserByLoginOrEmail($login);

		if (false !== $user) {
			return OTPUtils::enabledFor($user->ID);
		}

		// If this user does not exist, show the OTP field anyway
		return true;
	}

	public static function input_field(array $args)
	{
		$name    = Plugin::OPTIONS_KEY;
		$options = \get_option($name);
		$id      = \esc_attr($args['label_for']);
		$type    = \esc_attr($args['type'] ?? 'text');
		$value   = \esc_attr($options[$id] ?? '');
		$help    = $args['help'] ?? '';
		echo <<< EOT
<input type="{$type}" name="{$name}[{$id}]" id="{$id}" value="{$value}" class="regular-text"/>
EOT;
		if ($help) {
			echo <<< EOT
<p class="help">{$help}</p>
EOT;
		}
	}

	public static function textarea(array $args)
	{
		$name    = Plugin::OPTIONS_KEY;
		$options = \get_option($name);
		$id      = \esc_attr($args['label_for']);
		$value   = \esc_attr($options[$id] ?? '');
		$help    = $args['help'] ?? '';
		echo <<< EOT
<textarea name="{$name}[{$id}]" id="{$id}" rows="5" cols="40" class="regular-text">{$value}</textarea>
EOT;
		if ($help) {
			echo <<< EOT
<p class="help">{$help}</p>
EOT;
		}
	}

	public static function getSession() : array
	{
		$mgr     = \WP_Session_Tokens::get_instance(\get_current_user_id());
		$token   = \wp_get_session_token();
		$session = $mgr->get($token);

		return \is_array($session) ? $session : [];
	}

	public static function updateSession(array $values)
	{
		$mgr     = \WP_Session_Tokens::get_instance(\get_current_user_id());
		$token   = \wp_get_session_token();
		$session = $mgr->get($token);

		if (!$session) {
			return false;
		}

		foreach ($values as $k => $v) {
			if (null === $v) {
				unset($session[$k]);
			}
			else {
				$session[$k] = $v;
			}
		}

		$mgr->update($token, $session);
		return true;
	}
}
