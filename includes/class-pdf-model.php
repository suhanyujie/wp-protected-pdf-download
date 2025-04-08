<?php
class PDF_Model {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'protected_pdfs';
    }

    public function add_pdf($file_name, $file_url, $password) {
        global $wpdb;

        // 使用 WordPress 的密码哈希函数
        $hashed_password = wp_hash_password($password);

        return $wpdb->insert(
            $this->table_name,
            array(
                'file_name' => $file_name,
                'file_url' => $file_url,
                'password' => $hashed_password  // 存储哈希后的密码
            ),
            array('%s', '%s', '%s')
        );
    }

    public function get_pdf($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
    }

    public function get_pdf_by_url($url) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE file_url = %s", $url)
        );
    }

    public function update_pdf($id, $data) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
    }

    public function delete_pdf($id) {
        global $wpdb;
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }

    public function get_all_pdfs() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC");
    }

    public function increment_downloads($id) {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table_name} SET downloads = downloads + 1 WHERE id = %d",
                $id
            )
        );
    }

    // 添加密码验证方法
    public function verify_password($pdf_id, $password) {
        global $wpdb;
        $pdf = $this->get_pdf($pdf_id);
        if (!$pdf) {
            return false;
        }

        return wp_check_password($password, $pdf->password);
    }
}
