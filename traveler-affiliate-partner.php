
text/x-generic traveler-affiliate-partner.php ( PHP script, ASCII text, with CRLF line terminators )
<?php
/*
Plugin Name: Traveler Affiliate Partner - Backend Only
Description: Minimal backend for partner management, commission, and assignment.
Version: 1.0
Author: (Your Name)
*/

if (!defined('ABSPATH')) exit;

define('TAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TAP_PLUGIN_URL', plugin_dir_url(__FILE__));

define('PBP_PLUGIN_URL', TAP_PLUGIN_URL); // <-- ADD THIS LINE

// Only load backend logic!
// require_once TAP_PLUGIN_PATH . 'includes/utils.php'; // Only if you have this file!
require_once TAP_PLUGIN_PATH . 'includes/class-commission.php';
require_once TAP_PLUGIN_PATH . 'includes/commissions/tiers.php';
require_once TAP_PLUGIN_PATH . 'includes/admin_pages/partner-edit.php';

// Enqueue the admin select2 JS for the partner edit page
add_action('admin_enqueue_scripts', function($hook) {
    // Make sure this matches your actual admin page slug
    if ($hook === 'partners_page_pbp_affiliate_edit') {
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
        wp_enqueue_script('tap-admin-select-js', TAP_PLUGIN_URL . 'assets/pp-admin-select.js', ['jquery', 'select2'], '1.0.0', true);
        wp_localize_script('tap-admin-select-js', 'tap_admin_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tap_admin_search_nonce')
        ]);
    }
});
