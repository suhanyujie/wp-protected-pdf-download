<?php
class PDF_Upload {
    private $pdf_model;

    public function __construct($pdf_model) {
        $this->pdf_model = $pdf_model;
        $this->add_hooks();
    }

    private function add_hooks() {
        add_action('admin_post_upload_pdf', array($this, 'handle_upload'));
        // 支持从媒体库选择文件进行加密
        add_action('wp_ajax_handle_media_pdf', array($this, 'handle_media_pdf'));
    }

    public function handle_upload() {
        if (!current_user_can('manage_options')) {
            wp_die('権限がありません。');
        }

        check_admin_referer('upload_pdf_action', 'pdf_nonce');
        // 处理从媒体库选择的 PDF。从媒体库选择文件、上传文件，这2种方式二选一。
        if (!empty($_POST['media_pdf_id'])) {
            $this->handle_media_pdf_upload();
            return;
        }
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die('ファイルのアップロードに失敗しました。');
        }

        $password = isset($_POST['pdf_password']) ? sanitize_text_field($_POST['pdf_password']) : '';
        if (empty($password)) {
            wp_die('パスワードを入力してください。');
        }

        $file = $_FILES['pdf_file'];

        // 检查文件类型
        $file_type = wp_check_filetype($file['name'], array('pdf' => 'application/pdf'));
        if (!$file_type['type']) {
            wp_die('PDFファイルのみアップロード可能です。');
        }

        // 上传文件
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            wp_die($upload['error']);
        }

        // 保存到数据库
        $result = $this->pdf_model->add_pdf(
            $file['name'],
            $upload['url'],
            $password
        );

        if ($result === false) {
            // 如果保存失败，删除已上传的文件
            $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $upload['url']);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            wp_die('データベースの保存に失敗しました。');
        }

        // 重定向回设置页面
        wp_redirect(add_query_arg(
            array('page' => 'pdf-download-settings', 'uploaded' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }

    public function render_upload_form() {
        include PDF_PLUGIN_PATH . 'admin/partials/upload-form.php';
    }

    private function handle_media_pdf_upload() {
        $media_id = intval($_POST['media_pdf_id']);
        $password = isset($_POST['pdf_password']) ? sanitize_text_field($_POST['pdf_password']) : '';

        if (empty($password)) {
            wp_die('パスワードを入力してください。');
        }

        // 获取媒体文件信息
        $media = get_post($media_id);
        if (!$media || 'attachment' !== $media->post_type) {
            wp_die('無効なメディアファイルです。');
        }

        $file_url = wp_get_attachment_url($media_id);
        $file_name = basename($file_url);

        // 验证是否为PDF文件
        $file_type = wp_check_filetype($file_name, array('pdf' => 'application/pdf'));
        if (!$file_type['type']) {
            wp_die('PDFファイルのみアップロード可能です。');
        }

        // 保存到数据库
        $result = $this->pdf_model->add_pdf(
            $file_name,
            $file_url,
            $password
        );

        if ($result === false) {
            wp_die('データベースの保存に失敗しました。');
        }

        // 重定向回设置页面
        wp_redirect(add_query_arg(
            array('page' => 'pdf-download-settings', 'uploaded' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }
}
