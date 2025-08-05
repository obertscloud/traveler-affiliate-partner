<?php
// Folder: includes/admin_pages
// File: assign.php

if (!defined('ABSPATH')) exit;

// Main callback for Assign Tours submenu page in Affiliate Hub
function tap_assign_tours_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'traveler-affiliate-partner'));
    }

    $partners = get_users(['role' => 'partner']);
    $selected_partner = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Handle save
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_assignments'])) {
        $pid = intval($_POST['partner_id']);
        // Accept both Select2 (array) and CSV fallback for allowed tours/activities
        $allowed_tours = is_array($_POST['allowed_tours'] ?? null)
            ? array_map('intval', $_POST['allowed_tours'])
            : array_filter(array_map('intval', explode(',', $_POST['allowed_tours'] ?? '')));
        $allowed_activities = is_array($_POST['allowed_activities'] ?? null)
            ? array_map('intval', $_POST['allowed_activities'])
            : array_filter(array_map('intval', explode(',', $_POST['allowed_activities'] ?? '')));
        update_user_meta($pid, 'allowed_tours', $allowed_tours);
        update_user_meta($pid, 'allowed_activities', $allowed_activities);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Assignments updated.', 'traveler-affiliate-partner') . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Assign Tours & Activities', 'traveler-affiliate-partner'); ?></h1>
        <form method="get" action="">
            <input type="hidden" name="page" value="tap_assign_tours" />
            <label for="partner_select"><?php esc_html_e('Select Partner:', 'traveler-affiliate-partner'); ?></label>
            <select name="user_id" id="partner_select" style="min-width:220px;">
                <option value=""><?php esc_html_e('-- Choose --', 'traveler-affiliate-partner'); ?></option>
                <?php foreach ($partners as $partner): ?>
                    <option value="<?php echo intval($partner->ID); ?>" <?php selected($selected_partner, $partner->ID); ?>>
                        <?php echo esc_html($partner->display_name . ' (' . $partner->user_email . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button"><?php esc_html_e('Edit Assignments', 'traveler-affiliate-partner'); ?></button>
        </form>
        <?php
        if ($selected_partner) {
            $allowed_tours = get_user_meta($selected_partner, 'allowed_tours', true) ?: [];
            $allowed_activities = get_user_meta($selected_partner, 'allowed_activities', true) ?: [];
            ?>
            <hr/>
            <h2><?php esc_html_e('Assign for Partner #', 'traveler-affiliate-partner'); echo intval($selected_partner); ?></h2>
            <form method="post">
                <input type="hidden" name="partner_id" value="<?php echo intval($selected_partner); ?>" />
                <table class="form-table">
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
                <button class="button-primary" name="save_assignments"><?php esc_html_e('Save Assignments', 'traveler-affiliate-partner'); ?></button>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}