<?php
// Folder: includes/admin_pages
// File: commissions.php

if (!defined('ABSPATH')) exit;

// Main callback for Commission Tiers submenu page in Affiliate Hub
function tap_commission_tiers_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'traveler-affiliate-partner'));
    }

    $partners = get_users(['role' => 'partner']);
    $selected_partner = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Handle save
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tiers'])) {
        $pid = intval($_POST['partner_id']);
        $tiers = [];
        if (isset($_POST['tier_min'], $_POST['tier_max'], $_POST['tier_rate'])) {
            foreach ($_POST['tier_min'] as $i => $min) {
                $min = floatval($min);
                $max = floatval($_POST['tier_max'][$i]);
                $rate = floatval($_POST['tier_rate'][$i]);
                if ($rate > 0) {
                    $tiers[] = [
                        'min' => $min,
                        'max' => $max,
                        'rate' => $rate
                    ];
                }
            }
        }
        update_user_meta($pid, 'commission_tiers', $tiers);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Commission tiers updated.', 'traveler-affiliate-partner') . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Commission Tiers', 'traveler-affiliate-partner'); ?></h1>
        <form method="get" action="">
            <input type="hidden" name="page" value="tap_commission_tiers" />
            <label for="partner_select"><?php esc_html_e('Select Partner:', 'traveler-affiliate-partner'); ?></label>
            <select name="user_id" id="partner_select" style="min-width:220px;">
                <option value=""><?php esc_html_e('-- Choose --', 'traveler-affiliate-partner'); ?></option>
                <?php foreach ($partners as $partner): ?>
                    <option value="<?php echo intval($partner->ID); ?>" <?php selected($selected_partner, $partner->ID); ?>>
                        <?php echo esc_html($partner->display_name . ' (' . $partner->user_email . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button"><?php esc_html_e('Edit Tiers', 'traveler-affiliate-partner'); ?></button>
        </form>
        <?php
        if ($selected_partner) {
            $tiers = get_user_meta($selected_partner, 'commission_tiers', true) ?: [];
            ?>
            <hr/>
            <h2><?php esc_html_e('Commission Tiers for Partner #', 'traveler-affiliate-partner'); echo intval($selected_partner); ?></h2>
            <form method="post">
                <input type="hidden" name="partner_id" value="<?php echo intval($selected_partner); ?>" />
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Min', 'traveler-affiliate-partner'); ?></th>
                        <th><?php esc_html_e('Max', 'traveler-affiliate-partner'); ?></th>
                        <th><?php esc_html_e('Rate (%)', 'traveler-affiliate-partner'); ?></th>
                    </tr>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <tr>
                            <td><input type="number" step="0.01" name="tier_min[]" value="<?php echo esc_attr($tiers[$i]['min'] ?? ''); ?>" /></td>
                            <td><input type="number" step="0.01" name="tier_max[]" value="<?php echo esc_attr($tiers[$i]['max'] ?? ''); ?>" /></td>
                            <td><input type="number" step="0.01" name="tier_rate[]" value="<?php echo esc_attr($tiers[$i]['rate'] ?? ''); ?>" /></td>
                        </tr>
                    <?php endfor; ?>
                </table>
                <button class="button-primary" name="save_tiers"><?php esc_html_e('Save Tiers', 'traveler-affiliate-partner'); ?></button>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}