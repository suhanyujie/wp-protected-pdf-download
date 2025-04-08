<?php
class PDF_Admin {
    private $pdf_model;
    private $list_handler;
    private $edit_handler;
    private $upload_handler;

    public function __construct() {
        $this->pdf_model = new PDF_Model();
        $this->load_handlers();
        $this->add_hooks();
    }

    private function load_handlers() {
        require_once PDF_PLUGIN_PATH . 'includes/admin/class-pdf-list.php';
        require_once PDF_PLUGIN_PATH . 'includes/admin/class-pdf-edit.php';
        require_once PDF_PLUGIN_PATH . 'includes/admin/class-pdf-upload.php';

        $this->list_handler = new PDF_List($this->pdf_model);
        $this->edit_handler = new PDF_Edit($this->pdf_model);
        $this->upload_handler = new PDF_Upload($this->pdf_model);
    }

    private function add_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'PDF ダウンロード管理',
            'PDF ダウンロード管理',
            'manage_options',
            'pdf-download-settings',
            array($this, 'render_settings_page'),
            'dashicons-pdf'
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_pdf-download-settings' !== $hook) {
            return;
        }

        // 加载CSS
        wp_enqueue_style('dashicons');
        wp_enqueue_style('pdf-list', PDF_PLUGIN_URL . '/admin/css/pdf-list.css', array(), PDF_VERSION);
        wp_enqueue_style('pdf-modal', PDF_PLUGIN_URL . '/admin/css/pdf-modal.css', array(), PDF_VERSION);

        // 加载JS
        wp_enqueue_script('pdf-list', PDF_PLUGIN_URL . '/admin/js/pdf-list.js', array('jquery'), PDF_VERSION, true);
        wp_enqueue_script('pdf-edit', PDF_PLUGIN_URL . '/admin/js/pdf-edit.js', array('jquery'), PDF_VERSION, true);

        // 添加 nonce 和 ajaxurl
        wp_localize_script('pdf-edit', 'pdfAjax', array(
            'nonce' => wp_create_nonce('pdf_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function render_settings_page() {
        include PDF_PLUGIN_PATH . 'admin/partials/page-settings.php';
    }
}
