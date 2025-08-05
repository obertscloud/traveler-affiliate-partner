<?php
/*
Plugin Name: Traveler Affiliate Partner
Description: Custom backend interface for partners and admins to manage bookings, commissions, and access.
Version: 1.1
Author: Your Name
*/
if (!defined('ABSPATH')) exit;

define('PBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PBP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Core logic
require_once PBP_PLUGIN_PATH . 'includes/utils.php';
require_once PBP_PLUGIN_PATH . 'includes/class-commission.php';
require_once PBP_PLUGIN_PATH . 'includes/commission/tiers.php';

// Loader (handles admin assets, AJAX, CPT)
require_once PBP_PLUGIN_PATH . 'includes/loader.php';

// Load essential admin pages only
add_action('init', function() {
    require_once PBP_PLUGIN_PATH . 'includes/admin_pages/partner-edit.php';
    require_once PBP_PLUGIN_PATH . 'includes/admin_pages/commissions.php';
    // If you want other admin pages, uncomment below:
    // require_once PBP_PLUGIN_PATH . 'includes/admin_pages/bookings.php';
    // require_once PBP_PLUGIN_PATH . 'includes/admin_pages/dashboard.php';
});

// 🔎 AJAX handler for dynamic Select2 search (partner-edit)
add_action('wp_ajax_pp_admin_search_posts', function() {
    check_ajax_referer('pp_admin_search_nonce', 'nonce');

    $term = sanitize_text_field($_POST['term'] ?? '');
    $type = sanitize_text_field($_POST['post_type'] ?? '');
    $results = [];

    if ($term && in_array($type, ['st_tours', 'st_activity'])) {
        $query = new WP_Query([
            'post_type' => $type,
            's' => $term,
            'posts_per_page' => 20
        ]);

        foreach ($query->posts as $post) {
            $lang = get_post_meta($post->ID, 'language', true);
            $results[] = [
                'id'       => $post->ID,
                'label'    => $post->post_title,
                'language' => $lang
            ];
        }
    }

    wp_send_json($results);
});

// 🛠️ Admin-only tools: add menu and submenus INSIDE a hook!
add_action('admin_menu', function() {
    if (current_user_can('manage_options')) {
        add_menu_page(
            __('Affiliate Hub', 'partner-portal'),
            __('Affiliate Hub', 'partner-portal'),
            'manage_options',
            'pbp_assign',
            'pp_admin_affiliate_hub_page',
            'dashicons-networking',
            31
        );
        add_submenu_page('pbp_assign', __('Edit Partner', 'partner-portal'), __('Edit Partner', 'partner-portal'), 'manage_options', 'pbp_affiliate_edit', 'pp_admin_tour_partner_edit_page');
        add_submenu_page('pbp_assign', __('Commission Tiers', 'partner-portal'), __('Commission Tiers', 'partner-portal'), 'manage_options', 'pbp_commission_tiers', 'pp_admin_commission_tiers_page');
    }
});
