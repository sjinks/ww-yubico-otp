<?php

use WildWolf\WordPress\YubicoOTP\Admin;
use WildWolf\WordPress\YubicoOTP\AdminSettings;

defined( 'ABSPATH' ) || die(); ?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
	<?php
	settings_fields( AdminSettings::OPTION_GROUP );
	do_settings_sections( Admin::OPTIONS_MENU_SLUG );
	submit_button();
	?>
	</form>

	<p><?php echo wp_kses_post( __( 'You can sign up for the Client ID and Secret Key <a href="https://upgrade.yubico.com/getapikey/" target="_blank" rel="noopener">here</a>.', 'ww-yubiotp-admin' ) ); ?></p>
	<p><?php esc_html_e( 'Please make sure that Client ID matches the API Endpoints.', 'ww-yubiotp-admin' ); ?></p>
</div>
