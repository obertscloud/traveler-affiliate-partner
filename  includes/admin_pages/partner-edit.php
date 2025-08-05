<?php
// Folder: includes/admin_pages
// File: partner-edit.php


if (!defined('ABSPATH')) exit;

function pp_admin_tour_partner_edit_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'partner-portal'));
    }

    // Load Select2 when viewing the edit page
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

    // Get all users with role 'partner'
    $all_partners = get_users(['role' => 'partner']);
    $selected_partner = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Handle saving posted data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_partner_data'])) {
        $pid = intval($_POST['partner_id']);
        $commission = [
            'type'     => sanitize_text_field($_POST['commission_type']),
            'rate'     => floatval($_POST['commission_rate']),
            'schedule' => sanitize_text_field($_POST['commission_schedule'])
        ];
        $allowed_tours      = array_map('intval', $_POST['allowed_tours'] ?? []);
        $allowed_activities = array_map('intval', $_POST['allowed_activities'] ?? []);
        $tier_group         = sanitize_text_field($_POST['commission_tier_group'] ?? '');

        PBP_Utils::set_partner_commission($pid, $commission);
        PBP_Utils::set_partner_allowed_posts($pid, 'st_tours', $allowed_tours);
        PBP_Utils::set_partner_allowed_posts($pid, 'st_activity', $allowed_activities);
        update_user_meta($pid, 'pp_commission_tier_group', $tier_group);

        echo '<div class="updated"><p>Partner settings saved.</p></div>';
    }
?>
<div class="wrap">
    <h1><?php esc_html_e('Edit Partner', 'partner-portal'); ?></h1>

    <form method="get">
        <input type="hidden" name="page" value="pbp_affiliate_edit" />
        <select name="user_id" style="width: 400px;" class="js-select2">
            <option value=""><?php esc_html_e('Select partner', 'partner-portal'); ?></option>
            <?php foreach ($all_partners as $partner): ?>
                <?php
                    $type = function_exists('get_field') ? get_field('partner_type', 'user_' . $partner->ID) : 'unknown';
                ?>
                <option value="<?php echo esc_attr($partner->ID); ?>" <?php selected($selected_partner, $partner->ID); ?>>
                    <?php echo esc_html($partner->display_name . ' (' . $type . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button"><?php esc_html_e('Load', 'partner-portal'); ?></button>
    </form>

<?php if ($selected_partner): ?>
    <hr />
    <h2><?php esc_html_e('Partner Details', 'partner-portal'); ?></h2>
    <?php
        $commission = PBP_Utils::get_partner_commission($selected_partner);
        $allowed_tours = PBP_Utils::get_partner_allowed_posts($selected_partner, 'st_tours');
        $allowed_activities = PBP_Utils::get_partner_allowed_posts($selected_partner, 'st_activity');
        $tier_group = get_user_meta($selected_partner, 'pp_commission_tier_group', true);
    ?>
    <form method="post">
        <input type="hidden" name="partner_id" value="<?php echo esc_attr($selected_partner); ?>" />

        <table class="form-table">
            <tr>
                <th><label for="commission_type"><?php esc_html_e('Commission Type', 'partner-portal'); ?></label></th>
                <td><input type="text" name="commission_type" id="commission_type" value="<?php echo esc_attr($commission['type'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="commission_rate"><?php esc_html_e('Commission Rate (%)', 'partner-portal'); ?></label></th>
                <td><input type="number" step="0.01" name="commission_rate" id="commission_rate" value="<?php echo esc_attr($commission['rate'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="commission_schedule"><?php esc_html_e('Commission Schedule', 'partner-portal'); ?></label></th>
                <td><input type="text" name="commission_schedule" id="commission_schedule" value="<?php echo esc_attr($commission['schedule'] ?? ''); ?>" /></td>
            </tr>
            <tr>
                <th><label for="commission_tier_group"><?php esc_html_e('Tier Group', 'partner-portal'); ?></label></th>
                <td><input type="text" name="commission_tier_group" id="commission_tier_group" value="<?php echo esc_attr($tier_group); ?>" /></td>
            </tr>
            <tr>
                <th><label for="allowed_tours"><?php esc_html_e('Allowed Tours', 'partner-portal'); ?></label></th>
                <td>
                    <select name="allowed_tours[]" id="allowed_tours" multiple style="width: 100%;" class="js-select2">
                        <?php echo PBP_Utils::get_tours_options($allowed_tours); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="allowed_activities"><?php esc_html_e('Allowed Activities', 'partner-portal'); ?></label></th>
                <td>
                    <select name="allowed_activities[]" id="allowed_activities" multiple style="width: 100%;" class="js-select2">
                        <?php echo PBP_Utils::get_activities_options($allowed_activities); ?>
                    </select>
                </td>
            </tr>
        </table>

        <p><button type="submit" name="save_partner_data" class="button button-primary"><?php esc_html_e('Save Changes', 'partner-portal'); ?></button></p>
    </form>
<?php endif; ?>
</div>
<?php } ?>
