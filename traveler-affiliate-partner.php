<?php
/*
Plugin Name: Traveler Affiliate Partner
Description: Admin-only backend for managing affiliate/partner relationships, stripped from Partner Backend Portal. All frontend/partner-facing UI removed.
Version: 1.0.0
Author: obertscloud
*/

if (!defined('ABSPATH')) exit;

define('TAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load ONLY admin/affiliate backend logic
require_once TAP_PLUGIN_PATH . 'includes/admin/admin.php';
require_once TAP_PLUGIN_PATH . 'includes/admin_pages/partner-edit.php';
require_once TAP_PLUGIN_PATH . 'includes/admin_pages/assign.php';
require_once TAP_PLUGIN_PATH . 'includes/admin_pages/commissions.php';

// Load any other admin-page dependencies required for backend management
// (add other require_once lines here as needed for admin-only functionality)

// AJAX handler for Select2 partner/tour/activity search
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