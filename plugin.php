<?php
/*
 * Plugin Name: WW Yubico OTP Authentication
 * Plugin URI:
 * Description: Provides support for the Yubico OTP authentication
 * Version: 3.0.1
 * Author: Volodymyr Kolesnykov
 * License: MIT
 * Network:
 */

use Composer\Autoload\ClassLoader;

if ( defined( 'ABSPATH' ) ) {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		/** @var ClassLoader */
		$loader = require __DIR__ . '/vendor/autoload.php'; // NOSONAR
	} elseif ( file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
		/** @var ClassLoader */
		$loader = require ABSPATH . 'vendor/autoload.php';  // NOSONAR
	} else {
		return;
	}

	$loader->addClassMap( [
		WP_List_Table::class => ABSPATH . 'wp-admin/includes/class-wp-list-table.php',
	] );

	WildWolf\WordPress\YubicoOTP\Plugin::instance();
}
