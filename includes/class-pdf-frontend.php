<?php
class PDF_Frontend {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('pdf-frontend', PDF_PLUGIN_URL . '/assets/css/pdf-frontend.css', array(), PDF_VERSION);
        wp_enqueue_script('pdf-frontend', PDF_PLUGIN_URL . '/assets/js/pdf-frontend.js', array('jquery'), PDF_VERSION, true);
        
        wp_localize_script('pdf-frontend', 'pdfAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public static function render_download_link($attributes) {
        if (empty($attributes['pdfId'])) {
            return '';
        }

        $unique_id = uniqid('pdf_');
        
        ob_start();
        include PDF_PLUGIN_PATH . 'templates/frontend-download.php';
        return ob_get_clean();
    }

    private static function generate_token($pdf_url) {
        $token = wp_generate_password(32, false);
        set_transient('pdf_token_' . $token, array(
            'pdf_url' => $pdf_url,
            'password' => $passwords[$pdf_url]['password'] ?? ''
        ), HOUR_IN_SECONDS);
        return $token;
    }
} 