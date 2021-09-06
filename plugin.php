<?php
/*
 * Plugin Name: WW Yubico OTP Authentication
 * Plugin URI:
 * Description: Provides support for the Yubico OTP authentication
 * Version: 1.0.6
 * Author: Volodymyr Kolesnykov
 * License: MIT
 * Network:
 */

defined('ABSPATH') || die();

if (defined('VENDOR_PATH')) {
	require VENDOR_PATH . '/vendor/autoload.php';
}
elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require __DIR__ . '/vendor/autoload.php';
}
elseif (file_exists(ABSPATH . 'vendor/autoload.php')) {
	require ABSPATH . 'vendor/autoload.php';
}

WildWolf\WordPress\Autoloader::register();
WildWolf\YubicoOTP\Plugin::instance();
