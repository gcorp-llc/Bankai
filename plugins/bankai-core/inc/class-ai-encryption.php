<?php
/**
 * Bankai Core - AI Security & Encryption Helper
 *
 * Utilizes libsodium (sodium_crypto_secretbox) with OpenSSL AES-256-GCM fallback.
 * Derives encryption key via HKDF from BANKAI_ENCRYPTION_KEY or WP Salts.
 *
 * @package Bankai
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Bankai_AI_Encryption {

    /**
     * Derives a 32-byte secret key using HKDF-SHA256 from BANKAI_ENCRYPTION_KEY or WP Salts.
     */
    private static function get_secret_key(): string {
        if (defined('BANKAI_ENCRYPTION_KEY') && !empty(BANKAI_ENCRYPTION_KEY)) {
            $ikm = (string) BANKAI_ENCRYPTION_KEY;
        } else {
            $auth_key   = defined('AUTH_KEY') ? AUTH_KEY : 'bankai-fallback-auth-key';
            $sec_key    = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 'bankai-fallback-sec-key';
            $ikm        = $auth_key . '|' . $sec_key;
        }

        return hash_hkdf('sha256', $ikm, 32, 'bankai-ai-key-encryption', 'bankai-salt-v1');
    }

    /**
     * Encrypts plain API Key string.
     */
    public static function encrypt(string $plaintext): string {
        if (empty($plaintext)) {
            return '';
        }

        $key = self::get_secret_key();

        // 1. Try Libsodium if available
        if (function_exists('sodium_crypto_secretbox')) {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
            return 'sodium:' . base64_encode($nonce . $ciphertext);
        }

        // 2. Fallback to OpenSSL AES-256-GCM
        if (function_exists('openssl_encrypt')) {
            $iv = random_bytes(12);
            $tag = '';
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            return 'gcm:' . base64_encode($iv . $tag . $ciphertext);
        }

        // 3. Fallback to base64 if no crypto extensions present
        return 'plain:' . base64_encode($plaintext);
    }

    /**
     * Decrypts encrypted API Key string.
     */
    public static function decrypt(string $payload): string {
        if (empty($payload)) {
            return '';
        }

        $key = self::get_secret_key();

        if (str_starts_with($payload, 'sodium:')) {
            if (!function_exists('sodium_crypto_secretbox_open')) {
                return '';
            }
            $raw = base64_decode(substr($payload, 7));
            $nonce_byte_len = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
            if (strlen($raw) <= $nonce_byte_len) {
                return '';
            }
            $nonce = substr($raw, 0, $nonce_byte_len);
            $ciphertext = substr($raw, $nonce_byte_len);
            $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
            return $decrypted !== false ? $decrypted : '';
        }

        if (str_starts_with($payload, 'gcm:')) {
            if (!function_exists('openssl_decrypt')) {
                return '';
            }
            $raw = base64_decode(substr($payload, 4));
            if (strlen($raw) <= 28) { // 12 iv + 16 tag
                return '';
            }
            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $ciphertext = substr($raw, 28);
            $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            return $decrypted !== false ? $decrypted : '';
        }

        if (str_starts_with($payload, 'plain:')) {
            return base64_decode(substr($payload, 6));
        }

        // Unencrypted legacy fallback
        return $payload;
    }

    /**
     * Masks API Key for display in UI (e.g. sk-1234...5678)
     */
    public static function mask(string $plaintext): string {
        $len = strlen($plaintext);
        if ($len <= 8) {
            return '••••••••';
        }
        return substr($plaintext, 0, 4) . '••••••••' . substr($plaintext, -4);
    }
}
