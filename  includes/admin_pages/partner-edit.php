<?php
// Folder: includes/admin_pages
// File: partner-edit.php

if (!defined('ABSPATH')) exit;

// Main callback for Edit Partner submenu page in Affiliate Hub
function tap_partner_edit_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'traveler-affiliate-partner'));
    }

    // Load Select2 for partner selection and your admin AJAX select logic
    add_action('admin_enqueue_scripts', function($hook) {
        if ($hook === 'toplevel_page_tap_affiliate_hub' || $hook === 'affiliate-hub_page_tap_partner_edit') {
            wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
            wp_enqueue_script('tap-admin-select', TAP_PLUGIN_URL . 'assets/pp-admin-select.js', ['jquery', 'select2'], '1.0.0', true);
            wp_enqueue_style('tap-admin-style', TAP_PLUGIN_URL . 'assets/partner-portal.css');
            wp_localize_script('tap-admin-select', 'pp_admin_ajax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('pp_admin_search_nonce')
            ]);
        }
    });

    // Get all users with role 'partner'
    $all_partners = get_users(['role' => 'partner']);
    $selected_partner = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Handle saving posted data (commission, allowed tours, activities, etc.)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_partner_data'])) {
        $pid = intval($_POST['partner_id']);
        $commission = [
            'type'     => sanitize_text_field($_POST['commission_type']),
            'rate'     => floatval($_POST['commission_rate']),
            'schedule' => sanitize_text_field($_POST['commission_schedule'])
        ];

        // Accept both Select2 (array) and CSV fallback for allowed tours/activities
        $allowed_tours = is_array($_POST['allowed_tours'] ?? null)
            ? array_map('intval', $_POST['allowed_tours'])
            : array_filter(array_map('intval', explode(',', $_POST['allowed_tours'] ?? '')));
        $allowed_activities = is_array($_POST['allowed_activities'] ?? null)
            ? array_map('intval', $_POST['allowed_activities'])
            : array_filter(array_map('intval', explode(',', $_POST['allowed_activities'] ?? '')));

        update_user_meta($pid, 'commission', $commission);
        update_user_meta($pid, 'allowed_tours', $allowed_tours);
        update_user_meta($pid, 'allowed_activities', $allowed_activities);

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Partner data updated.', 'traveler-affiliate-partner') . '</p></div>';
    }

    // Output form for selecting and editing a partner (minimal version)
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Edit Partner', 'traveler-affiliate-partner'); ?></h1>
        <form method="get" action="">
            <input type="hidden" name="page" value="tap_partner_edit" />
            <label for="partner_select"><?php esc_html_e('Select Partner:', 'traveler-affiliate-partner'); ?></label>
            <select name="user_id" id="partner_select" style="min-width:220px;">
                <option value=""><?php esc_html_e('-- Choose --', 'traveler-affiliate-partner'); ?></option>
                <?php foreach ($all_partners as $partner): ?>
                    <option value="<?php echo intval($partner->ID); ?>" <?php selected($selected_partner, $partner->ID); ?>>
                        <?php echo esc_html($partner->display_name . ' (' . $partner->user_email . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button"><?php esc_html_e('Edit', 'traveler-affiliate-partner'); ?></button>
        </form>
        <?php
        if ($selected_partner) {
            $commission = get_user_meta($selected_partner, 'commission', true);
            $allowed_tours = get_user_meta($selected_partner, 'allowed_tours', true) ?: [];
            $allowed_activities = get_user_meta($selected_partner, 'allowed_activities', true) ?: [];
            ?>
            <hr/>
            <h2><?php esc_html_e('Edit Details for Partner #', 'traveler-affiliate-partner'); echo intval($selected_partner); ?></h2>
            <form method="post">
                <input type="hidden" name="partner_id" value="<?php echo intval($selected_partner); ?>" />
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Commission Type', 'traveler-affiliate-partner'); ?></th>
                        <td><input type="text" name="commission_type" value="<?php echo esc_attr($commission['type'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Commission Rate (%)', 'traveler-affiliate-partner'); ?></th>
                        <td><input type="number" step="0.01" name="commission_rate" value="<?php echo esc_attr($commission['rate'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Commission Schedule', 'traveler-affiliate-partner'); ?></th>
                        <td><input type="text" name="commission_schedule" value="<?php echo esc_attr($commission['schedule'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Allowed Tours', 'traveler-affiliate-partner'); ?></th>
                        <td>
                            <select id="allowed_tours" name="allowed_tours[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($allowed_tours as $tid): ?>
                                    <?php if ($tid): ?>
                                        <option value="<?php echo esc_attr($tid); ?>" selected="selected"><?php echo esc_html($tid); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small><?php esc_html_e('Search & select tours. Hold Ctrl (Cmd) for multiple.', 'traveler-affiliate-partner'); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Allowed Activities', 'traveler-affiliate-partner'); ?></th>
                        <td>
                            <select id="allowed_activities" name="allowed_activities[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($allowed_activities as $aid): ?>
                                    <?php if ($aid): ?>
                                        <option value="<?php echo esc_attr($aid); ?>" selected="selected"><?php echo esc_html($aid); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small><?php esc_html_e('Search & select activities. Hold Ctrl (Cmd) for multiple.', 'traveler-affiliate-partner'); ?></small>
                        </td>
                    </tr>
                </table>
                <button class="button-primary" name="save_partner_data"><?php esc_html_e('Save Partner Data', 'traveler-affiliate-partner'); ?></button>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}