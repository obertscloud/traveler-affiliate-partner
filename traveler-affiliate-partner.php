
text/x-generic traveler-affiliate-partner.php ( PHP script, ASCII text, with CRLF line terminators )
<?php
/*
Plugin Name: Traveler Affiliate Partner - Backend Only
Description: Minimal backend for partner management, commission, and assignment. Admins can view dashboard, affiliate hub, commissions, bookings, and edit partners.
Version: 1.0
Author: (Your Name)
*/

if (!defined('ABSPATH')) exit;

define('PBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PBP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Core includes
require_once PBP_PLUGIN_PATH . 'includes/utils.php';
require_once PBP_PLUGIN_PATH . 'includes/class-commission.php';
require_once PBP_PLUGIN_PATH . 'includes/commission/tiers.php';
require_once PBP_PLUGIN_PATH . 'includes/admin_pages/partner-edit.php';

// Admin menu and submenus
add_action('admin_menu', function() {
    if (!current_user_can('manage_options')) return;

    // Main admin dashboard page
    add_menu_page(
        __('Affiliate Hub', 'partner-portal'),
        __('Affiliate Hub', 'partner-portal'),
        'manage_options',
        'pbp_assign',
        'pp_admin_affiliate_hub_page',
        'dashicons-networking',
        30
    );

    add_submenu_page(
        'pbp_assign',
        __('Edit Partner', 'partner-portal'),
        __('Edit Partner', 'partner-portal'),
        'manage_options',
        'pbp_affiliate_edit',
        'pp_admin_tour_partner_edit_page'
    );

    add_submenu_page(
        'pbp_assign',
        __('Commission Tiers', 'partner-portal'),
        __('Commission Tiers', 'partner-portal'),
        'manage_options',
        'pbp_commission_tiers',
        'pp_admin_commission_tiers_page'
    );

    add_submenu_page(
        'pbp_assign',
        __('Bookings', 'partner-portal'),
        __('Bookings', 'partner-portal'),
        'manage_options',
        'pbp_bookings',
        'pp_admin_bookings_page'
    );

    add_submenu_page(
        'pbp_assign',
        __('Dashboard', 'partner-portal'),
        __('Dashboard', 'partner-portal'),
        'manage_options',
        'pbp_dashboard',
        'pp_admin_dashboard_page'
    );
});

// Enqueue the admin select2 JS for the partner edit page
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'partners_page_pbp_affiliate_edit') {
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
        wp_enqueue_script('pp-admin-select-js', PBP_PLUGIN_URL . 'assets/pp-admin-select.js', ['jquery', 'select2'], '1.0.0', true);
        wp_localize_script('pp-admin-select-js', 'pp_admin_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pp_admin_search_nonce')
        ]);
    }
});
