<?php
// Seo Optimizer - Admin Menu

// exit if file is called directly
if (!defined('ABSPATH')) {
    exit;
}

// add top level administrative menu
if (!function_exists('seo_optimizer_add_top_level_menu')) {
    function seo_optimizer_add_top_level_menu()
    {
        /*
    add_menu_page(
        string  $page_title,
        string  $menu_title,
        string  $capability,
        string  $menu_slug,
        callable    $function = '',
        string  $icon_url = '',
        int     $position = null
    )
     */
        add_menu_page(
            'Seo Optimizer',
            'SEO Optimizer',
            'manage_options',
            'seo-optimizer',
            'seo_optimizer_display_settings_page',
            'dashicons-admin-generic',
            null
        );
    }
    add_action('admin_menu', 'seo_optimizer_add_top_level_menu');
}
