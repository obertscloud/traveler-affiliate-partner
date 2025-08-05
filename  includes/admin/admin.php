<?php
// Folder: includes/admin
// File: admin.php

if (!defined('ABSPATH')) exit;

// Admin menu for Affiliate Hub, stripped to admin-only backend logic
add_action('admin_menu', function() {
    if (!current_user_can('manage_options')) return;

    // Main Affiliate Hub menu in sidebar
    add_menu_page(
        __('Affiliate Hub', 'traveler-affiliate-partner'),
        __('Affiliate Hub', 'traveler-affiliate-partner'),
        'manage_options',
        'tap_affiliate_hub',
        'tap_admin_affiliate_hub_page',
        'dashicons-networking',
        31
    );

    // Submenus for managing partners, tour assignment, and commissions
    add_submenu_page('tap_affiliate_hub', __('Edit Partner', 'traveler-affiliate-partner'), __('Edit Partner', 'traveler-affiliate-partner'), 'manage_options', 'tap_partner_edit', 'tap_admin_partner_edit_page');
    add_submenu_page('tap_affiliate_hub', __('Assign Tours', 'traveler-affiliate-partner'), __('Assign Tours', 'traveler-affiliate-partner'), 'manage_options', 'tap_assign_tours', 'tap_admin_assign_tours_page');
    add_submenu_page('tap_affiliate_hub', __('Commission Tiers', 'traveler-affiliate-partner'), __('Commission Tiers', 'traveler-affiliate-partner'), 'manage_options', 'tap_commission_tiers', 'tap_admin_commission_tiers_page');
});

// Main Affiliate Hub page callback (placeholder)
function tap_admin_affiliate_hub_page() {
    echo '<div class="wrap"><h1>' . esc_html__('Affiliate Hub', 'traveler-affiliate-partner') . '</h1><p>' . esc_html__('Welcome to the Affiliate Hub. Use the menu to manage partners, assign tours, and set commissions.', 'traveler-affiliate-partner') . '</p></div>';
}

// Submenu stubs, actual content in admin_pages files
function tap_admin_partner_edit_page()      { require_once TAP_PLUGIN_PATH . 'includes/admin_pages/partner-edit.php';      tap_partner_edit_page(); }
function tap_admin_assign_tours_page()      { require_once TAP_PLUGIN_PATH . 'includes/admin_pages/assign.php';           tap_assign_tours_page(); }
function tap_admin_commission_tiers_page()  { require_once TAP_PLUGIN_PATH . 'includes/admin_pages/commissions.php';      tap_commission_tiers_page(); }

add_action('admin_enqueue_scripts', function($hook) {
    // Only load on your plugin's admin pages
    $valid_hooks = [
        'toplevel_page_tap_affiliate_hub',
        'affiliate-hub_page_tap_partner_edit',
        'affiliate-hub_page_tap_assign_tours',
        'affiliate-hub_page_tap_commission_tiers'
    ];
    if (!in_array($hook, $valid_hooks)) return;

    // Select2
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
    // Your admin select logic
    wp_enqueue_script('tap-admin-select', TAP_PLUGIN_URL . 'assets/pp-admin-select.js', ['jquery', 'select2'], '1.0.0', true);
    // Your CSS (optional)
    wp_enqueue_style('tap-admin-style', TAP_PLUGIN_URL . 'assets/partner-portal.css');
    // Localize for AJAX if needed
    wp_localize_script('tap-admin-select', 'pp_admin_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('pp_admin_search_nonce')
    ]);
});