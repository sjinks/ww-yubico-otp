<?php
namespace WildWolf\YubicoOTP;

final class KeyTable extends \WP_List_Table
{
	/**
	 * @var int
	 */
	private $user_id;

	public function __construct($args = [])
	{
		$this->user_id = $args['user_id'] ?? 0;
		unset($args['user_id']);
		parent::__construct($args);
	}

	public function prepare_items()
	{
		$this->items = OTPUtils::keysFor($this->user_id);
	}

	public function get_columns()
	{
		return [
			'name'      => \__('Key Name', 'ww-yubiotp-admin'),
			'key'       => \__('Key ID', 'ww-yubiotp-admin'),
			'created'   => \__('Created', 'ww-yubiotp-admin'),
			'last_used' => \__('Last Used', 'ww-yubiotp-admin'),
			'actions'   => '',
		];
	}

	protected function column_name($item) : string
	{
		$actions = [
			'revoke' => \sprintf(
				'<button class="button-link hide-if-no-js revoke-button" data-key="%1$s" data-nonce="%2$s">%3$s <span class="spinner"></span></button>',
				$item['key'],
				\wp_create_nonce('revoke-key_' . $item['key']),
				\__('Revoke', 'ww-yubiotp-admin')
			),
		];

		return
			  \esc_html($item['name'])
			. $this->row_actions($actions, false)
		;
	}

	protected function column_key($item) : string
	{
		return \esc_html($item['key']);
	}

	protected function column_created($item) : string
	{
		return self::handleDateColumn($item, 'created');
	}

	protected function column_last_used($item) : string
	{
		return self::handleDateColumn($item, 'last_used');
	}

	protected function column_actions($item) : string
	{
		$key    = \esc_attr($item['key']);
		$action = \esc_attr(\admin_url('admin-post.php'));
		$revoke = \esc_attr(\__('Revoke', 'ww-yubiotp-admin'));
		$nonce  = \wp_nonce_field('revoke-key_' . $item['key'], '_wpnonce', true, false);
		$nonce  = \str_replace(' id="_wpnonce"', '', $nonce);
		$s = <<< EOT
<form action="{$action}" method="post" class="hide-if-js">
	<input type="hidden" name="action" value="wwyotp_revoke_key"/>
	<input type="hidden" name="key" value="{$key}"/>
	<input type="submit" class="button" value="{$revoke}"/>
	{$nonce}
</form>
EOT;
		return $s;
	}

	private static function handleDateColumn($item, string $idx) : string
	{
		$date_format = (string)\get_option('date_format', 'r');
		$time_format = (string)\get_option('time_format', 'r');
		return \date_i18n($date_format . ' ' . $time_format, $item[$idx]);
	}

	protected function display_tablenav($which)
	{
	}
}
