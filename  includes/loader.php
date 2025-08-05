if (!defined('ABSPATH')) exit;

// 🔌 Enqueue plugin assets
add_action('admin_enqueue_scripts', function($hook) {
    if (!strpos($hook, 'pbp_')) return;
    wp_enqueue_style('pp_styles', PBP_PLUGIN_URL . 'includes/assets/partner-portal.css');
    wp_enqueue_script('pp_scripts', PBP_PLUGIN_URL . 'includes/assets/partner-portal.js', ['jquery'], null, true);
    wp_localize_script('pp_scripts', 'pp_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('pp_ajax_nonce')
    ]);
});

 
});