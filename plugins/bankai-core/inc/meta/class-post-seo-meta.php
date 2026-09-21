<?php
/**
 * Compatibility shim — meta registration lives in Bankai_SEO_Engine.
 * Kept so older loaders that require this file do not fatal.
 */
defined('ABSPATH') || exit;

class Bankai_Post_SEO_Meta
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct()
    {
        // Meta is registered exclusively by Bankai_SEO_Engine::register_meta().
    }
}
