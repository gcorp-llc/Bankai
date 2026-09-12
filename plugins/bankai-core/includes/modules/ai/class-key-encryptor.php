<?php
/**
 * AES-256-GCM Key Encryptor for API Keys.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Key_Encryptor
 */
class Bankai_Key_Encryptor {

	/**
	 * Cipher method.
	 */
	const CIPHER = 'aes-256-gcm';

	/**
	 * Get encryption key derived from WordPress salts.
	 *
	 * @return string 32-byte key.
	 */
	private static function get_master_key() {
		$auth_key  = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'bankai-fallback-auth-key-salt';
		$secure_salt = defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : 'bankai-fallback-secure-salt';
		return hash_hkdf( 'sha256', $auth_key, 32, 'bankai-ai-keys', $secure_salt );
	}

	/**
	 * Encrypt plain text API key.
	 *
	 * @param string $plain_text Plain text key.
	 * @return string|bool Encrypted payload (base64) or false on failure.
	 */
	public static function encrypt( $plain_text ) {
		if ( empty( $plain_text ) ) {
			return '';
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $plain_text ); // Fallback if openssl unavailable
		}

		try {
			$key = self::get_master_key();
			$iv  = openssl_random_pseudo_bytes( 12 ); // 12 bytes for AES-GCM
			$tag = '';

			$cipher_text = openssl_encrypt( $plain_text, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16 );

			if ( false === $cipher_text ) {
				return false;
			}

			// Format: IV(12) + TAG(16) + CIPHER_TEXT
			$payload = $iv . $tag . $cipher_text;
			return base64_encode( $payload );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Decrypt encrypted payload.
	 *
	 * @param string $encrypted_payload Base64 encoded payload.
	 * @return string Decrypted plain text key or empty string on failure.
	 */
	public static function decrypt( $encrypted_payload ) {
		if ( empty( $encrypted_payload ) ) {
			return '';
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return base64_decode( $encrypted_payload );
		}

		try {
			$decoded = base64_decode( $encrypted_payload, true );
			if ( false === $decoded || strlen( $decoded ) < 28 ) {
				return '';
			}

			$key         = self::get_master_key();
			$iv          = substr( $decoded, 0, 12 );
			$tag         = substr( $decoded, 12, 16 );
			$cipher_text = substr( $decoded, 28 );

			$plain_text = openssl_decrypt( $cipher_text, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag );
			return false !== $plain_text ? $plain_text : '';
		} catch ( Exception $e ) {
			return '';
		}
	}
}
