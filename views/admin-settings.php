<?php defined( 'ABSPATH' ) || die(); ?>
<div class="wrap">
	<h1><?php esc_html_e( 'Yubico OTP', 'ww-yubiotp-admin' ); ?></h1>

	<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
	<?php
	settings_fields( 'ww_yubico_otp' );
	do_settings_sections( 'ww_yubico_otp' );
	submit_button();
	?>
	</form>

	<p><?php echo wp_kses_post( __( 'You can sign up for the Client ID and Secret Key <a href="https://upgrade.yubico.com/getapikey/" target="_blank" rel="noopener">here</a>.', 'ww-yubiotp-admin' ) ); ?></p>
	<p><?php esc_html_e( 'Please make sure that Client ID matches the API Endpoints.', 'ww-yubiotp-admin' ); ?></p>
</div>
