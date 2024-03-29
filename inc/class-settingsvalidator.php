<?php

namespace WildWolf\WordPress\YubicoOTP;

/**
 * @psalm-import-type SettingsArray from Settings
 */
abstract class SettingsValidator {
	/**
	 * @psalm-param mixed[] $settings
	 * @psalm-return SettingsArray
	 */
	public static function ensure_data_shape( array $settings ): array {
		$defaults = Settings::defaults();
		$result   = $settings + $defaults;
		foreach ( $result as $key => $_value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $result[ $key ] );
			}
		}

		/** @var mixed $value */
		foreach ( $result as $key => $value ) {
			$my_type    = gettype( $value );
			$their_type = gettype( $defaults[ $key ] );
			if ( $my_type !== $their_type ) {
				settype( $result[ $key ], $their_type );
			}
		}

		/** @psalm-var SettingsArray */
		return $result;
	}

	/**
	 * @param mixed $settings
	 * @psalm-return SettingsArray $settings
	 */
	public static function sanitize( $settings ): array {
		if ( is_array( $settings ) ) {
			$settings = self::ensure_data_shape( $settings );

			$settings['client_id'] = (string) filter_var( $settings['client_id'], FILTER_VALIDATE_INT, [
				'options' => [
					'default'   => 0,
					'min_range' => 0,
				],
			] );

			if ( '0' === $settings['client_id'] ) {
				$settings['client_id'] = '';
			}

			$endpoint = filter_var( trim( $settings['endpoint'] ), FILTER_VALIDATE_URL );
			if ( is_string( $endpoint ) && ! preg_match( '!https?://!i', $endpoint ) ) {
				$endpoint = false;
			}

			$settings['endpoint'] = $endpoint ? $endpoint : '';
			return $settings;
		}

		return Settings::defaults();
	}
}
