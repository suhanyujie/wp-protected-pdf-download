<?php
class PDF_Ajax {
    private $pdf_model;

    public function __construct() {
        $this->pdf_model = new PDF_Model();
        add_action('wp_ajax_verify_pdf_password', array($this, 'verify_password'));
        add_action('wp_ajax_nopriv_verify_pdf_password', array($this, 'verify_password'));
        add_action('wp_ajax_upload_pdf_file', array($this, 'handle_upload'));
    }

    public function verify_password() {
        $submitted_password = $_POST['password'] ?? '';
        $pdf_id = intval($_POST['pdf_id'] ?? 0);
        
        $pdf = $this->pdf_model->get_pdf($pdf_id);
        if (!$pdf) {
            wp_send_json_error(array('message' => 'PDFファイルが見つかりません。'));
            return;
        }

        if ($this->pdf_model->verify_password($pdf_id, $submitted_password)) {
            $this->pdf_model->increment_downloads($pdf_id);
            wp_send_json_success(array('url' => $pdf->file_url));
        } else {
            wp_send_json_error(array('message' => 'パスワードが正しくありません。'));
        }
    }

    public function handle_upload() {
        check_ajax_referer('upload_pdf_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '権限がありません。'));
        }

        // 处理文件上传逻辑...
    }
} 