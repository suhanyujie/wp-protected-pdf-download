<?php
class PDF_Admin {
    private $pdf_model;

    public function __construct() {
        $this->pdf_model = new PDF_Model();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_post_upload_pdf', array($this, 'handle_pdf_upload'));
        add_action('wp_ajax_reset_pdf_password', array($this, 'handle_password_reset'));
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

    public function register_settings() {
        register_setting('pdf_download_options', 'pdf_download_passwords');
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_pdf-download-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('pdf-admin', PDF_PLUGIN_URL . '/assets/css/pdf-admin.css', array(), PDF_VERSION);
        wp_enqueue_script('pdf-admin', PDF_PLUGIN_URL . '/assets/js/pdf-admin.js', array('jquery'), PDF_VERSION, true);
    }

    public function handle_pdf_upload() {
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません。');
        }

        check_admin_referer('upload_pdf_action', 'pdf_nonce');

        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die('ファイルのアップロードに失敗しました。');
        }

        $password = isset($_POST['pdf_password']) ? sanitize_text_field($_POST['pdf_password']) : '';
        if (empty($password)) {
            wp_die('パスワードを入力してください。');
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $file = $_FILES['pdf_file'];
        
        $file_type = wp_check_filetype($file['name'], array('pdf' => 'application/pdf'));
        if (!$file_type['type']) {
            wp_die('PDFファイルのみアップロード可能です。');
        }

        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_die($upload['error']);
        }

        $result = $this->pdf_model->add_pdf(
            $file['name'],
            $upload['url'],
            $password
        );

        if ($result === false) {
            wp_die('データベースの保存に失敗しました。');
        }

        wp_redirect(add_query_arg(
            array('page' => 'pdf-download-settings', 'uploaded' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }

    public function handle_password_reset() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
        }

        $pdf_id = intval($_POST['pdf_id'] ?? 0);
        $new_password = wp_generate_password(12, false); // 生成新密码
        
        $pdf = $this->pdf_model->get_pdf($pdf_id);
        if (!$pdf) {
            wp_send_json_error(array('message' => 'PDFが見つかりません。'));
            return;
        }

        // 更新密码
        $result = $this->pdf_model->update_pdf($pdf_id, array(
            'password' => wp_hash_password($new_password)
        ));

        if ($result) {
            wp_send_json_success(array(
                'message' => 'パスワードが更新されました。',
                'new_password' => $new_password
            ));
        } else {
            wp_send_json_error(array('message' => 'パスワードの更新に失敗しました。'));
        }
    }

    public function render_settings_page() {
        if (isset($_GET['uploaded']) && $_GET['uploaded'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p>PDFファイルが正常にアップロードされました。</p></div>';
        }
        
        include PDF_PLUGIN_PATH . 'templates/admin-settings.php';
    }
} 