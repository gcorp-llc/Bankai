<?php
/**
 * REST API Endpoint Handler.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Rest_API
 */
class Bankai_Rest_API {

	/**
	 * Namespace for Bankai REST API.
	 */
	const NAMESPACE = 'bankai/v1';

	/**
	 * Initialize REST API hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		// Modules Route
		register_rest_route(
			self::NAMESPACE,
			'/modules',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_modules' ),
					'permission_callback' => array( __CLASS__, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'update_modules' ),
					'permission_callback' => array( __CLASS__, 'permissions_check' ),
				),
			)
		);

		// AI API Keys Route
		register_rest_route(
			self::NAMESPACE,
			'/ai/keys',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'save_ai_key' ),
					'permission_callback' => array( __CLASS__, 'permissions_check' ),
				),
			)
		);

		// AI Query Test Route
		register_rest_route(
			self::NAMESPACE,
			'/ai/generate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'generate_ai_content' ),
					'permission_callback' => array( __CLASS__, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check user permissions for REST API endpoints.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|WP_Error True if permitted, WP_Error otherwise.
	 */
	public static function permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'bankai_rest_forbidden',
				esc_html__( 'You do not have sufficient permissions to access Bankai Core settings.', 'bankai-core' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * GET /bankai/v1/modules callback.
	 */
	public static function get_modules( $request ) {
		$modules = Bankai_Module_Switcher::get_modules();
		return rest_ensure_response(
			array(
				'success' => true,
				'modules' => $modules,
			)
		);
	}

	/**
	 * POST /bankai/v1/modules callback.
	 */
	public static function update_modules( $request ) {
		$params  = $request->get_json_params();
		$modules = isset( $params['modules'] ) && is_array( $params['modules'] ) ? $params['modules'] : array();

		$updated = Bankai_Module_Switcher::update_modules( $modules );

		return rest_ensure_response(
			array(
				'success' => $updated,
				'modules' => Bankai_Module_Switcher::get_modules(),
				'message' => $updated ? esc_html__( 'Module settings updated successfully.', 'bankai-core' ) : esc_html__( 'No changes made.', 'bankai-core' ),
			)
		);
	}

	/**
	 * POST /bankai/v1/ai/keys callback.
	 */
	public static function save_ai_key( $request ) {
		$params   = $request->get_json_params();
		$provider = isset( $params['provider'] ) ? sanitize_key( $params['provider'] ) : '';
		$key      = isset( $params['key'] ) ? trim( $params['key'] ) : '';

		if ( empty( $provider ) || empty( $key ) ) {
			return new WP_Error( 'bankai_invalid_params', esc_html__( 'Provider and API key are required.', 'bankai-core' ), array( 'status' => 400 ) );
		}

		$encrypted = Bankai_Key_Encryptor::encrypt( $key );
		if ( false === $encrypted ) {
			return new WP_Error( 'bankai_encryption_error', esc_html__( 'Failed to encrypt API key.', 'bankai-core' ), array( 'status' => 500 ) );
		}

		update_option( "bankai_ai_{$provider}_api_key", $encrypted );

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => sprintf( esc_html__( 'API key for %s saved securely.', 'bankai-core' ), $provider ),
			)
		);
	}

	/**
	 * POST /bankai/v1/ai/generate callback.
	 */
	public static function generate_ai_content( $request ) {
		$params   = $request->get_json_params();
		$provider = isset( $params['provider'] ) ? sanitize_key( $params['provider'] ) : 'openai';
		$prompt   = isset( $params['prompt'] ) ? sanitize_text_field( $params['prompt'] ) : '';

		if ( empty( $prompt ) ) {
			return new WP_Error( 'bankai_empty_prompt', esc_html__( 'Prompt cannot be empty.', 'bankai-core' ), array( 'status' => 400 ) );
		}

		$result = Bankai_AI_Client::query( $provider, $prompt );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}
}

Bankai_Rest_API::init();
