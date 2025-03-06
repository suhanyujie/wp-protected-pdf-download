<?php
/*
Plugin Name: Protected PDF Download
Plugin URI: https://github.com/suhanyujie/wp-protected-pdf-download
Description: パスワード付きPDFダウンロードリンクを追加
Version: 1.0
Author: suhanyujie
Author URI: https://github.com/suhanyujie
Text Domain: https://github.com/suhanyujie/wp-protected-pdf-download
*/

if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('PDF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PDF_PLUGIN_URL', plugins_url('', __FILE__));
define('PDF_VERSION', '1.0.0');

// 加载核心类文件
require_once PDF_PLUGIN_PATH . 'includes/class-pdf-model.php';
require_once PDF_PLUGIN_PATH . 'includes/class-pdf-admin.php';
require_once PDF_PLUGIN_PATH . 'includes/class-pdf-block.php';
require_once PDF_PLUGIN_PATH . 'includes/class-pdf-frontend.php';
require_once PDF_PLUGIN_PATH . 'includes/class-pdf-ajax.php';

// 初始化插件
class Protected_PDF_Download {
    private static $instance = null;
    private $db_version = '1.0';
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'protected_pdfs';
        $this->init();
    }

    private function init() {
        // 初始化各个组件
        new PDF_Admin();
        new PDF_Block();
        new PDF_Frontend();
        new PDF_Ajax();

        // 激活钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function activate() {
        $this->create_tables();
        add_option('pdf_db_version', $this->db_version);
    }

    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_name varchar(255) NOT NULL,
            file_url text NOT NULL,
            password varchar(255) NOT NULL,
            downloads int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY file_name (file_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// 启动插件
Protected_PDF_Download::get_instance();
