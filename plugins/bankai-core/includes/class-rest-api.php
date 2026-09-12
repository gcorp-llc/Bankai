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
     *
     * @param WP_REST_Request|null $request Request object.
     * @return bool|WP_Error
     */
    public static function permissions_check( $request = null ) {
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
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
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
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public static function update_modules( $request ) {
        $params       = $request->get_json_params();
        $modules_data = isset( $params['modules'] ) && is_array( $params['modules'] ) ? $params['modules'] : array();

        $updated = Bankai_Module_Switcher::update_modules( $modules_data );

        return rest_ensure_response(
            array(
                'success' => $updated,
                'modules' => Bankai_Module_Switcher::get_modules(),
                'message' => $updated 
                    ? esc_html__( 'Module settings updated successfully.', 'bankai-core' ) 
                    : esc_html__( 'No changes made.', 'bankai-core' ),
            )
        );
    }

    /**
     * GET /bankai/v1/dashboard/overview callback.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public static function get_dashboard_overview( $request ) {
        $modules_states   = Bankai_Module_Switcher::get_modules();
        $active_count     = count( array_filter( $modules_states ) );
        $total_count      = count( $modules_states );

        $upload_dir       = wp_upload_dir();
        $uploads_writable = wp_is_writable( $upload_dir['basedir'] );

        $host_name        = class_exists( 'Bankai_Host_Detector' ) ? Bankai_Host_Detector::detect() : 'Standard WordPress Server';

        $ai_keys          = array(
            'openai'     => (bool) get_option( 'bankai_ai_openai_api_key' ),
            'gemini'     => (bool) get_option( 'bankai_ai_gemini_api_key' ),
            'claude'     => (bool) get_option( 'bankai_ai_claude_api_key' ),
            'deepseek'   => (bool) get_option( 'bankai_ai_deepseek_api_key' ),
            'openrouter' => (bool) get_option( 'bankai_ai_openrouter_api_key' ),
        );

        return rest_ensure_response( array(
            'success'      => true,
            'systemHealth' => array(
                'apiStatus'       => 'operational',
                'uploadsWritable' => $uploads_writable,
                'hostName'        => $host_name,
            ),
            'modules'      => array(
                'active' => $active_count,
                'total'  => $total_count,
                'states' => $modules_states,
            ),
            'seo'          => array(
                'sitemapUrl' => home_url( '/sitemap.xml' ),
                'llmsUrl'    => home_url( '/llms.txt' ),
                'schemas'    => array( 'Article', 'Product', 'FAQ', 'HowTo', 'LocalBusiness', 'Recipe', 'Course', 'Event', 'Video', 'Podcast' ),
            ),
            'ai'           => array(
                'keys'           => $ai_keys,
                'activeProvider' => get_option( 'bankai_ai_default_provider', 'gemini' ),
            ),
            'branding'     => array(
                'logoUrl' => class_exists( 'Bankai_Branding' ) ? Bankai_Branding::get_logo_url() : '',
            ),
        ) );
    }

    /**
     * POST /bankai/v1/ai/keys callback.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public static function save_ai_key( $request ) {
        $params   = $request->get_json_params();
        $provider = isset( $params['provider'] ) ? sanitize_key( $params['provider'] ) : '';
        $raw_key  = isset( $params['key'] ) ? trim( $params['key'] ) : '';

        if ( empty( $provider ) || empty( $raw_key ) ) {
            return new WP_Error(
                'bankai_invalid_params',
                esc_html__( 'Provider and API key are required.', 'bankai-core' ),
                array( 'status' => 400 )
            );
        }

        $key_to_save = class_exists( 'Bankai_Key_Encryptor' ) ? Bankai_Key_Encryptor::encrypt( $raw_key ) : $raw_key;
        update_option( "bankai_ai_{$provider}_api_key", $key_to_save );

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => sprintf( esc_html__( 'API key for %s saved securely.', 'bankai-core' ), $provider ),
            )
        );
    }

    /**
     * POST /bankai/v1/ai/generate callback.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public static function generate_ai_content( $request ) {
        $params   = $request->get_json_params();
        $provider = isset( $params['provider'] ) ? sanitize_key( $params['provider'] ) : 'openai';
        $prompt   = isset( $params['prompt'] ) ? sanitize_text_field( $params['prompt'] ) : '';

        if ( empty( $prompt ) ) {
            return new WP_Error(
                'bankai_empty_prompt',
                esc_html__( 'Prompt cannot be empty.', 'bankai-core' ),
                array( 'status' => 400 )
            );
        }

        $result = class_exists( 'Bankai_AI_Client' ) 
            ? Bankai_AI_Client::query( $provider, $prompt ) 
            : array( 'response' => 'AI simulation result.' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( $result );
    }
}

Bankai_Rest_API::init();