<?php
class PDF_Edit {
    private $pdf_model;

    public function __construct($pdf_model) {
        $this->pdf_model = $pdf_model;
        $this->add_hooks();
    }

    private function add_hooks() {
        add_action('wp_ajax_edit_pdf', array($this, 'handle_edit'));
    }

    public function handle_edit() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
        }

        check_ajax_referer('pdf_ajax_nonce', 'security');

        $pdf_id = intval($_POST['pdf_id'] ?? 0);
        $file_name = sanitize_text_field($_POST['file_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($file_name)) {
            wp_send_json_error(array('message' => 'ファイル名を入力してください。'));
            return;
        }

        // 获取现有PDF信息
        $pdf = $this->pdf_model->get_pdf($pdf_id);
        if (!$pdf) {
            wp_send_json_error(array('message' => 'PDFが見つかりません。'));
            return;
        }

        $update_data = array('file_name' => $file_name);
        
        // 如果提供了新密码，则更新密码
        if (!empty($password)) {
            $update_data['password'] = wp_hash_password($password);
        }

        $result = $this->pdf_model->update_pdf($pdf_id, $update_data);

        if ($result !== false) {
            wp_send_json_success(array('message' => '更新が完了しました。'));
        } else {
            wp_send_json_error(array('message' => '更新に失敗しました。'));
        }
    }

    public function render_edit_modal() {
        include PDF_PLUGIN_PATH . 'admin/partials/edit-modal.php';
    }
} 