<?php

namespace WildWolf\WordPress\YubicoOTP;

use WP_List_Table;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Key_Table extends WP_List_Table {
	private int $user_id;

	/**
	 * @param mixed[] $args
	 * @psalm-param array{ajax?: bool, plural?: string, screen?: string, singular?: string, user_id?: int} $args
	 * @psalm-suppress RedundantCastGivenDocblockType
	 */
	public function __construct( $args = [] ) {
		$this->user_id = (int) ( $args['user_id'] ?? 0 );
		unset( $args['user_id'] );
		parent::__construct( $args );
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		$this->items = OTP_Utils::keys_for( $this->user_id );
	}

	/**
	 * @return array<string,string>
	 */
	public function get_columns() {
		return [
			'name'      => __( 'Key Name', 'ww-yubiotp-admin' ),
			'key'       => __( 'Key ID', 'ww-yubiotp-admin' ),
			'created'   => __( 'Created', 'ww-yubiotp-admin' ),
			'last_used' => __( 'Last Used', 'ww-yubiotp-admin' ),
			'actions'   => '',
		];
	}

	/**
	 * @param string[] $item
	 */
	protected function column_name( $item ): string {
		$actions = [
			'revoke' => sprintf(
				'<button class="button-link hide-if-no-js revoke-button" data-key="%1$s" data-nonce="%2$s">%3$s <span class="spinner"></span></button>',
				$item['key'],
				wp_create_nonce( 'revoke-key_' . $item['key'] ),
				__( 'Revoke', 'ww-yubiotp-admin' )
			),
		];

		return esc_html( $item['name'] )
			. $this->row_actions( $actions, false );
	}

	/**
	 * @param string[] $item
	 */
	protected function column_key( $item ): string {
		return esc_html( $item['key'] );
	}

	/**
	 * @param string[] $item
	 */
	protected function column_created( $item ): string {
		return self::handle_date_column( $item, 'created' );
	}

	/**
	 * @param string[] $item
	 */
	protected function column_last_used( $item ): string {
		return self::handle_date_column( $item, 'last_used' );
	}

	/**
	 * @param string[] $item
	 */
	protected function column_actions( $item ): string {
		$key    = esc_attr( $item['key'] );
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$revoke = esc_attr( __( 'Revoke', 'ww-yubiotp-admin' ) );
		$nonce  = wp_nonce_field( 'revoke-key_' . $item['key'], '_wpnonce', true, false );
		$nonce  = str_replace( ' id="_wpnonce"', '', $nonce );
		return <<< EOT
<form action="{$action}" method="post" class="hide-if-js">
	<input type="hidden" name="action" value="wwyotp_revoke_key"/>
	<input type="hidden" name="key" value="{$key}"/>
	<input type="submit" class="button" value="{$revoke}"/>
	{$nonce}
</form>
EOT;
	}

	/**
	 * @param string[] $item
	 */
	private static function handle_date_column( $item, string $idx ): string {
		$date_format = (string) get_option( 'date_format', 'r' );
		$time_format = (string) get_option( 'time_format', 'r' );
		return date_i18n( $date_format . ' ' . $time_format, (int) $item[ $idx ] );
	}

	/**
	 * @param string $_which
	 * @return void
	 */
	protected function display_tablenav( $_which ) {
		/* Do nothing */
	}
}
