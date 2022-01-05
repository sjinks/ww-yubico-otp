<?php

namespace WildWolf\WordPress\YubicoOTP;

use ArrayAccess;
use LogicException;
use WildWolf\Utils\Singleton;

/**
 * @psalm-type SettingsArray = array{
 *  client_id: string,
 *  secret_key: string,
 *  endpoint: string
 * }
 *
 * @template-implements ArrayAccess<string, scalar>
 */
final class Settings implements ArrayAccess {
	use Singleton;

	/** @var string  */
	public const OPTION_KEY = 'ww_yubico_otp';

	/**
	 * @psalm-readonly
	 * @psalm-var SettingsArray
	 */
	private static $defaults = [
		'client_id'  => '',
		'secret_key' => '',
		'endpoint'   => '',
	];

	/**
	 * @var array
	 * @psalm-var SettingsArray
	 */
	private $options;

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->refresh();
	}

	public function refresh(): void {
		/** @var mixed */
		$settings      = get_option( self::OPTION_KEY );
		$this->options = SettingsValidator::ensure_data_shape( is_array( $settings ) ? $settings : [] );
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public static function defaults(): array {
		return self::$defaults;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->options[ (string) $offset ] );
	}

	/**
	 * @param mixed $offset
	 * @return int|string|bool|null
	 */
	public function offsetGet( $offset ) {
		return $this->options[ (string) $offset ] ?? null;
	}

	/**
	 * @param mixed $_offset
	 * @param mixed $_value
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetSet( $_offset, $_value ): void {
		throw new LogicException();
	}

	/**
	 * @param mixed $_offset
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetUnset( $_offset ): void {
		throw new LogicException();
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public function as_array(): array {
		return $this->options;
	}

	public function get_client_id(): string {
		return $this->options['client_id'];
	}

	public function get_secret_key(): string {
		return $this->options['secret_key'];
	}

	public function get_endpoint(): string {
		return $this->options['endpoint'];
	}
}
