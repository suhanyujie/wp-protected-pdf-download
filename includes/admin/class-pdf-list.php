<?php
class PDF_List {
    private $pdf_model;

    public function __construct($pdf_model) {
        $this->pdf_model = $pdf_model;
        $this->add_hooks();
    }

    private function add_hooks() {
        add_action('wp_ajax_delete_pdf', array($this, 'handle_delete'));
        add_action('wp_ajax_reset_pdf_password', array($this, 'handle_password_reset'));
    }

    public function get_pdfs() {
        return $this->pdf_model->get_all_pdfs();
    }

    public function handle_delete() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
        }

        check_ajax_referer('pdf_ajax_nonce', 'security');

        $pdf_id = intval($_POST['pdf_id'] ?? 0);
        
        // 获取PDF信息以删除文件
        $pdf = $this->pdf_model->get_pdf($pdf_id);
        if (!$pdf) {
            wp_send_json_error(array('message' => 'PDFが見つかりません。'));
            return;
        }

        // 删除文件
        $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $pdf->file_url);
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // 删除数据库记录
        $result = $this->pdf_model->delete_pdf($pdf_id);
        if ($result) {
            wp_send_json_success(array('message' => 'PDFファイルが削除されました。'));
        } else {
            wp_send_json_error(array('message' => '削除に失敗しました。'));
        }
    }

    public function handle_password_reset() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
        }

        check_ajax_referer('pdf_ajax_nonce', 'security');

        $pdf_id = intval($_POST['pdf_id'] ?? 0);
        $new_password = wp_generate_password(12, false);
        
        $pdf = $this->pdf_model->get_pdf($pdf_id);
        if (!$pdf) {
            wp_send_json_error(array('message' => 'PDFが見つかりません。'));
            return;
        }

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

    public function render_list() {
        $pdfs = $this->get_pdfs();
        include PDF_PLUGIN_PATH . 'admin/partials/list-table.php';
    }
} 