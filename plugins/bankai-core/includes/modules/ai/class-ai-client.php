<?php
/**
 * Multi-Provider AI Client (OpenAI, Gemini, Claude, DeepSeek, OpenRouter).
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_AI_Client
 */
class Bankai_AI_Client {

	/**
	 * Send prompt request to selected AI provider.
	 *
	 * @param string $provider Provider name (openai, gemini, claude, deepseek, openrouter).
	 * @param string $prompt   Prompt text.
	 * @param array  $options  Extra parameters (model, max_tokens, etc).
	 * @return array|WP_Error Array response or WP_Error.
	 */
	public static function query( $provider, $prompt, $options = array() ) {
		$encrypted_key = get_option( "bankai_ai_{$provider}_api_key", '' );
		$api_key       = Bankai_Key_Encryptor::decrypt( $encrypted_key );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'bankai_ai_missing_key', sprintf( esc_html__( 'API key for provider %s is missing or invalid.', 'bankai-core' ), $provider ) );
		}

		switch ( strtolower( $provider ) ) {
			case 'openai':
				return self::query_openai( $api_key, $prompt, $options );
			case 'gemini':
				return self::query_gemini( $api_key, $prompt, $options );
			case 'claude':
				return self::query_claude( $api_key, $prompt, $options );
			case 'deepseek':
				return self::query_deepseek( $api_key, $prompt, $options );
			case 'openrouter':
				return self::query_openrouter( $api_key, $prompt, $options );
			default:
				return new WP_Error( 'bankai_ai_unknown_provider', esc_html__( 'Unsupported AI provider.', 'bankai-core' ) );
		}
	}

	/**
	 * Query OpenAI API.
	 */
	private static function query_openai( $api_key, $prompt, $options ) {
		$model = isset( $options['model'] ) ? $options['model'] : 'gpt-4o-mini';

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'    => $model,
						'messages' => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return array(
				'success' => true,
				'text'    => trim( $data['choices'][0]['message']['content'] ),
			);
		}

		return new WP_Error( 'bankai_ai_response_error', esc_html__( 'Failed to retrieve valid response from OpenAI.', 'bankai-core' ) );
	}

	/**
	 * Query Google Gemini API.
	 */
	private static function query_gemini( $api_key, $prompt, $options ) {
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 30,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'contents' => array(
							array(
								'parts' => array(
									array( 'text' => $prompt ),
								),
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return array(
				'success' => true,
				'text'    => trim( $data['candidates'][0]['content']['parts'][0]['text'] ),
			);
		}

		return new WP_Error( 'bankai_ai_response_error', esc_html__( 'Failed to retrieve valid response from Gemini.', 'bankai-core' ) );
	}

	/**
	 * Query Anthropic Claude API.
	 */
	private static function query_claude( $api_key, $prompt, $options ) {
		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 30,
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type'      => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => 'claude-3-haiku-20240307',
						'max_tokens' => 1000,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['content'][0]['text'] ) ) {
			return array(
				'success' => true,
				'text'    => trim( $data['content'][0]['text'] ),
			);
		}

		return new WP_Error( 'bankai_ai_response_error', esc_html__( 'Failed to retrieve valid response from Claude.', 'bankai-core' ) );
	}

	/**
	 * Query DeepSeek API.
	 */
	private static function query_deepseek( $api_key, $prompt, $options ) {
		$response = wp_remote_post(
			'https://api.deepseek.com/chat/completions',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'    => 'deepseek-chat',
						'messages' => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return array(
				'success' => true,
				'text'    => trim( $data['choices'][0]['message']['content'] ),
			);
		}

		return new WP_Error( 'bankai_ai_response_error', esc_html__( 'Failed to retrieve valid response from DeepSeek.', 'bankai-core' ) );
	}

	/**
	 * Query OpenRouter API.
	 */
	private static function query_openrouter( $api_key, $prompt, $options ) {
		$response = wp_remote_post(
			'https://openrouter.ai/api/v1/chat/completions',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'    => isset( $options['model'] ) ? $options['model'] : 'auto',
						'messages' => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return array(
				'success' => true,
				'text'    => trim( $data['choices'][0]['message']['content'] ),
			);
		}

		return new WP_Error( 'bankai_ai_response_error', esc_html__( 'Failed to retrieve valid response from OpenRouter.', 'bankai-core' ) );
	}
}
