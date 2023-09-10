<?php

namespace WildWolf\WordPress\YubicoOTP;

use WP_User;

abstract class WP_Utils {
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	public static function render( string $view, array $params = [] ): void {
		/** @psalm-suppress UnresolvableInclude */
		require __DIR__ . '/../views/' . $view . '.php'; // NOSONAR
	}

	public static function assets_url( string $file ): string {
		return plugins_url( 'assets/' . $file, dirname( __DIR__ ) . '/plugin.php' );
	}

	public static function get_user_by_login_or_email( string $s ): ?WP_User {
		$user = get_user_by( 'login', $s );
		if ( ! $user && strpos( $s, '@' ) ) {
			$user = get_user_by( 'email', $s );
		}

		return $user ? $user : null;
	}

	public static function get_post_var_as_string( string $key ): string {
		/** @psalm-suppress RedundantCast */
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? (string) $_POST[ $key ] : '';
	}
}
