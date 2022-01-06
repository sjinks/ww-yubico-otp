<?php defined( 'ABSPATH' ) || die(); ?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<noscript>
		<div class="hide-if-js error" id="message">
			<p><?php esc_html_e( 'This page needs JavaScript.', 'ww-yubiotp-admin' ); ?></p>
		</div>
	</noscript>

	<div class="hide-if-no-js">
		<h2 id="registered-keys"><?php esc_html_e( 'Registered Keys', 'ww-yubiotp-admin' ); ?></h2>
<?php
$table = new WildWolf\WordPress\YubicoOTP\Key_Table( [
	'screen'  => 'yotp',
	'user_id' => get_current_user_id(),
] );
$table->prepare_items();
$table->display();
?>

		<h2 id="new-key"><?php esc_html_e( 'Add a New Key', 'ww-yubiotp-admin' ); ?></h2>

		<form id="new-key-form" action="#" method="post">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="key-name"><?php esc_html_e( 'Key Name', 'ww-yubiotp-admin' ); ?></label></th>
						<td><input type="text" id="key-name" required="required" name="yotp[name]" value="" class="regular-text"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="otp"><?php esc_html_e( 'Yubico One Time Password', 'ww-yubiotp-admin' ); ?></label>
						<td>
							<textarea id="otp" required="required" name="yotp[otp]" spellcheck="false" autocomplete="off" enterkeyhint="next" rows="2" cols="50" class="regular-text"></textarea>
							<p class="help">
								<?php esc_html_e( 'Please insert and tap your YubiKey to get the one time password.', 'ww-yubiotp-admin' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Key', 'ww-yubiotp-admin' ); ?>" id="submit-button"/>
				<input type="hidden" name="action" value="wwyotp_add_key"/>
				<?php wp_nonce_field( 'wwyotp-add_key' ); ?>
				<span class="spinner" style="float: none"></span>
			</p>
		</form>
	</div>
</div>

<script type="text/x-template" id="tpl-empty">
	<tr class="no-items"><td class="colspanchange" colspan="5">
	<?php 
	ob_start();
	$table->no_items();
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</td></tr>
</script>
