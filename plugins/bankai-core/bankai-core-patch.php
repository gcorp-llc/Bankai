<?php
/**
 * Apply these two changes inside Bankai_Core.
 */

// 1) load_dependencies() — after class-rest-api.php:
// require_once BANKAI_CORE_DIR . 'inc/class-dashboard-stats.php';

// 2) init_hooks() — after Bankai_Rest_API::instance():
// if (class_exists('Bankai_Dashboard_Stats')) {
//     Bankai_Dashboard_Stats::instance();
// }

// 3) Optionally gate SEO module boot:
/*
if (class_exists('Bankai_SEO_Engine') && function_exists('bankai_is_module_active') && bankai_is_module_active('seo_engine')) {
    Bankai_SEO_Engine::instance();
}
*/
