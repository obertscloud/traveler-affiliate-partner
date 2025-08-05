<?php
// File: includes/class-utils.php

if (!defined('ABSPATH')) exit;

class PBP_Utils {
    public static function is_partner($user_id) {
        $user = get_user_by('ID', $user_id);
        return $user && in_array('partner', (array) $user->roles);
    }

    public static function get_partner_commission($user_id) {
        return get_user_meta($user_id, 'pp_commission', true) ?: [];
    }

    public static function set_partner_commission($user_id, $commission) {
        update_user_meta($user_id, 'pp_commission', $commission);
    }

    public static function get_partner_allowed_posts($user_id, $post_type) {
        return get_user_meta($user_id, "pp_allowed_{$post_type}", true) ?: [];
    }

    public static function set_partner_allowed_posts($user_id, $post_type, $ids) {
        update_user_meta($user_id, "pp_allowed_{$post_type}", $ids);
    }

    public static function get_tours_options($selected = []) {
        // You should implement this!
        return '';
    }

    public static function get_activities_options($selected = []) {
        // You should implement this!
        return '';
    }
}