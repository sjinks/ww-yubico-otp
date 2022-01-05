<?php
/*
 * Plugin Name: WW Yubico OTP Authentication
 * Plugin URI:
 * Description: Provides support for the Yubico OTP authentication
 * Version: 2.0.0
 * Author: Volodymyr Kolesnykov
 * License: MIT
 * Network:
 */

if ( defined( 'ABSPATH' ) ) {
	if ( defined( 'VENDOR_PATH' ) ) {
		/** @psalm-suppress UnresolvableInclude */
		require VENDOR_PATH . '/vendor/autoload.php';
	} elseif ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	} elseif ( file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
		/** @psalm-suppress UnresolvableInclude */
		require ABSPATH . 'vendor/autoload.php';
	}

	WildWolf\WordPress\Autoloader::register();
	WildWolf\WordPress\YubicoOTP\Plugin::instance();
}
