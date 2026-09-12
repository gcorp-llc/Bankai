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

		// Overview Dashboard Stats
		register_rest_route(
			self::NAMESPACE,
			'/dashboard/overview',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_dashboard_overview' ),
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
	 */
	public static function permissions_check(  ) {
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
	public static function get_modules(  ) {
		 = Bankai_Module_Switcher::get_modules();
		return rest_ensure_response(
			array(
				'success' => true,
				'modules' => ,
			)
		);
	}

	/**
	 * POST /bankai/v1/modules callback.
	 */
	public static function update_modules(  ) {
		  = ->get_json_params();
		 = isset( ['modules'] ) && is_array( ['modules'] ) ? ['modules'] : array();

		 = Bankai_Module_Switcher::update_modules(  );

		return rest_ensure_response(
			array(
				'success' => ,
				'modules' => Bankai_Module_Switcher::get_modules(),
				'message' =>  ? esc_html__( 'Module settings updated successfully.', 'bankai-core' ) : esc_html__( 'No changes made.', 'bankai-core' ),
			)
		);
	}

	/**
	 * GET /bankai/v1/dashboard/overview callback.
	 */
	public static function get_dashboard_overview(  ) {
		 = Bankai_Module_Switcher::get_modules();
		 = count( array_filter(  ) );
		  = count(  );

		 = wp_upload_dir();
		 = wp_is_writable( ['basedir'] );

		 = class_exists( 'Bankai_Host_Detector' ) ? Bankai_Host_Detector::detect() : 'Standard WordPress Server';

		 = array(
			'openai'     => (bool) get_option( 'bankai_ai_openai_api_key' ),
			'gemini'     => (bool) get_option( 'bankai_ai_gemini_api_key' ),
			'claude'     => (bool) get_option( 'bankai_ai_claude_api_key' ),
			'deepseek'   => (bool) get_option( 'bankai_ai_deepseek_api_key' ),
			'openrouter' => (bool) get_option( 'bankai_ai_openrouter_api_key' ),
		);

		return rest_ensure_response( array(
			'success' => true,
			'systemHealth' => array(
				'apiStatus'       => 'operational',
				'uploadsWritable' => ,
				'hostName'        => ,
			),
			'modules' => array(
				'active' => ,
				'total'  => ,
				'states' => ,
			),
			'seo' => array(
				'sitemapUrl' => home_url( '/sitemap.xml' ),
				'llmsUrl'    => home_url( '/llms.txt' ),
				'schemas'    => array( 'Article', 'Product', 'FAQ', 'HowTo', 'LocalBusiness', 'Recipe', 'Course', 'Event', 'Video', 'Podcast' ),
			),
			'ai' => array(
				'keys'          => ,
				'activeProvider'=> get_option( 'bankai_ai_default_provider', 'gemini' ),
			),
			'branding' => array(
				'logoUrl' => class_exists('Bankai_Branding') ? Bankai_Branding::get_logo_url() : '',
			)
		) );
	}

	/**
	 * POST /bankai/v1/ai/keys callback.
	 */
	public static function save_ai_key(  ) {
		   = ->get_json_params();
		 = isset( ['provider'] ) ? sanitize_key( ['provider'] ) : '';
		      = isset( ['key'] ) ? trim( ['key'] ) : '';

		if ( empty(  ) || empty(  ) ) {
			return new WP_Error( 'bankai_invalid_params', esc_html__( 'Provider and API key are required.', 'bankai-core' ), array( 'status' => 400 ) );
		}

		 = class_exists( 'Bankai_Key_Encryptor' ) ? Bankai_Key_Encryptor::encrypt(  ) : ;
		update_option( "bankai_ai_{}_api_key",  );

		return rest_ensure_response(
			array(
				'success'  => true,
				'message'  => sprintf( esc_html__( 'API key for %s saved securely.', 'bankai-core' ),  ),
			)
		);
	}

	/**
	 * POST /bankai/v1/ai/generate callback.
	 */
	public static function generate_ai_content(  ) {
		   = ->get_json_params();
		 = isset( ['provider'] ) ? sanitize_key( ['provider'] ) : 'openai';
		   = isset( ['prompt'] ) ? sanitize_text_field( ['prompt'] ) : '';

		if ( empty(  ) ) {
			return new WP_Error( 'bankai_empty_prompt', esc_html__( 'Prompt cannot be empty.', 'bankai-core' ), array( 'status' => 400 ) );
		}

		 = class_exists( 'Bankai_AI_Client' ) ? Bankai_AI_Client::query( ,  ) : array( 'response' => 'AI simulation result.' );

		if ( is_wp_error(  ) ) {
			return ;
		}

		return rest_ensure_response(  );
	}
}

Bankai_Rest_API::init();
