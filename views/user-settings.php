<?php defined('ABSPATH') || die(); ?>
<div class="wrap">
	<h1><?=\__('Yubico OTP', 'ww-yubiotp-admin'); ?></h1>

<?php if (!empty($params['error'])) : ?>
	<div id="message" class="error fade">
		<p><?=$params['error'];?></p>
	</div>
<?php endif; ?>

<?php if (!empty($params['message'])) : ?>
	<div id="message" class="updated">
		<p><?=$params['message'];?></p>
	</div>
<?php endif; ?>

	<h2 id="registered-keys"><?=__('Registered Keys', 'ww-yubiotp-admin'); ?></h2>
<?php
$table = new WildWolf\YubicoOTP\KeyTable(['screen' => 'yotp', 'user_id' => get_current_user_id()]);
$table->prepare_items();
$table->display();
?>

	<h2 id="new-key"><?=__('Add a New Key', 'ww-yubiotp-admin'); ?></h2>

	<form id="new-key-form" action="<?=esc_attr(admin_url('admin-post.php'));?>" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="key-name"><?=__('Key Name', 'ww-yubiotp-admin'); ?></label></th>
					<td><input type="text" id="key-name" required="required" name="yotp[name]" value="<?=esc_attr($params['name']);?>"/></td>
				</tr>
				<tr>
					<th scope="row"><label for="otp"><?=__('Yubico One Time Password', 'ww-yubiotp-admin'); ?></label>
					<td>
						<textarea id="otp" required="required" name="yotp[otp]" spellcheck="false" autocomplete="off" enterkeyhint="next" rows="2" cols="50"></textarea>
						<p class="help">
							<?=__('Please insert and tap your YubiKey to get the one time password.', 'ww-yubiotp-admin'); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" class="button button-primary" value="<?=__('Add Key', 'ww-yubiotp-admin'); ?>" id="submit-button"/>
			<input type="hidden" name="action" value="wwyotp_add_key"/>
			<?php wp_nonce_field('wwyotp-add_key'); ?>
			<span class="spinner" style="float: none"></span>
		</p>
	</form>
</div>

<script type="text/x-template" id="tpl-empty">
	<tr class="no-items"><td class="colspanchange" colspan="5"><?php ob_start(); $table->no_items(); echo ob_get_clean(); ?></td></tr>
</script>
