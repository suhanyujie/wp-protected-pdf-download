<?php
/*
Plugin Name: Protected PDF Download
Description: パスワード付きPDFダウンロードリンクを追加
Version: 1.0
Author: Your Name
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 注册短代码和必要的脚本
function pdf_download_init() {
    add_shortcode('protected_pdf', 'protected_pdf_shortcode');
    // 移除条件判断，确保脚本在前后台都加载
    add_action('wp_enqueue_scripts', 'pdf_download_scripts');
}
add_action('init', 'pdf_download_init');

// 加载必要的 JS 和 CSS
function pdf_download_scripts() {
    // 加载前台 JS
    wp_enqueue_script(
        'pdf-download-js', 
        plugins_url('js/pdf-download.js', __FILE__), 
        array('jquery'), 
        filemtime(plugin_dir_path(__FILE__) . 'js/pdf-download.js'), 
        true
    );
    
    // 加载前台 CSS
    wp_enqueue_style(
        'pdf-download-css', 
        plugins_url('css/pdf-download.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/pdf-download.css')
    );
    
    // 添加 AJAX URL
    wp_localize_script('pdf-download-js', 'pdfAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    
    // 加载 dashicons
    wp_enqueue_style('dashicons');
}

// 添加管理菜单
function pdf_download_admin_menu() {
    add_menu_page(
        'PDF ダウンロード管理',
        'PDF ダウンロード管理',
        'manage_options',
        'pdf-download-settings',
        'pdf_download_settings_page',
        'dashicons-pdf'
    );
}
add_action('admin_menu', 'pdf_download_admin_menu');

// 注册设置
function pdf_download_register_settings() {
    register_setting('pdf_download_options', 'pdf_download_passwords');
}
add_action('admin_init', 'pdf_download_register_settings');

// 设置页面HTML
function pdf_download_settings_page() {
    // 获取已保存的密码
    $passwords = get_option('pdf_download_passwords', array());
    ?>
    <div class="wrap">
        <h1>PDF ダウンロードパスワード管理</h1>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php settings_fields('pdf_download_options'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>PDF ファイル</th>
                        <th style="width: 200px;">パスワード</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="pdf-passwords-list">
                    <?php
                    if (!empty($passwords)) {
                        foreach ($passwords as $file_url => $data) {
                            echo '<tr>
                                <td>
                                    <input type="text" name="pdf_download_passwords[' . esc_attr($file_url) . '][url]" value="' . esc_attr($file_url) . '" style="width: 100%;" readonly />
                                    <span class="filename">' . esc_html(basename($file_url)) . '</span>
                                </td>
                                <td>
                                    <div class="password-field-wrapper" style="position: relative;">
                                        <input type="password" 
                                               name="pdf_download_passwords[' . esc_attr($file_url) . '][password]" 
                                               value="' . esc_attr($data['password']) . '"
                                               class="password-input" 
                                               style="width: calc(100% - 30px);" />
                                        <span class="toggle-password dashicons dashicons-visibility" 
                                              style="position: absolute; right: 5px; top: 5px; cursor: pointer;"
                                              title="パスワードを表示"></span>
                                    </div>
                                </td>
                                <td><button type="button" class="button remove-pdf">削除</button></td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div class="pdf-upload-section" style="margin: 20px 0;">
                <h3>新しいPDFファイルを追加</h3>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">PDFファイルをアップロード:</label>
                    <input type="file" id="pdf_file" accept=".pdf" style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">パスワード:</label>
                    <div class="password-field-wrapper" style="position: relative;">
                        <input type="password" 
                               id="new_password" 
                               style="margin-bottom: 10px; width: 200px;" />
                        <span class="toggle-password dashicons dashicons-visibility" 
                              style="position: absolute; right: 5px; top: 5px; cursor: pointer;"
                              title="パスワードを表示"></span>
                    </div>
                    <button type="button" class="button button-secondary" id="upload-pdf">アップロード</button>
                </div>
            </div>
            <?php submit_button('すべての変更を保存'); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // 添加密码显示切换功能
        $(document).on('click', '.toggle-password', function() {
            var $input = $(this).siblings('input');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $(this).attr('title', 'パスワードを隠す');
            } else {
                $input.attr('type', 'password');
                $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $(this).attr('title', 'パスワードを表示');
            }
        });

        // PDF上传处理
        $('#upload-pdf').click(function() {
            var fileInput = $('#pdf_file')[0];
            var password = $('#new_password').val();
            
            if (!fileInput.files.length) {
                alert('PDFファイルを選択してください。');
                return;
            }
            if (!password) {
                alert('パスワードを入力してください。');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'upload_pdf_file');
            formData.append('pdf_file', fileInput.files[0]);
            formData.append('password', password);
            formData.append('security', '<?php echo wp_create_nonce("upload_pdf_nonce"); ?>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'アップロードに失敗しました。');
                    }
                },
                error: function() {
                    alert('エラーが発生しました。');
                }
            });
        });

        // 删除PDF
        $(document).on('click', '.remove-pdf', function() {
            if (confirm('本当に削除しますか？')) {
                $(this).closest('tr').remove();
            }
        });
    });
    </script>
    <?php
}

// 处理PDF上传的AJAX函数
function handle_pdf_upload() {
    check_ajax_referer('upload_pdf_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '権限がありません。'));
    }

    if (!isset($_FILES['pdf_file']) || !isset($_POST['password'])) {
        wp_send_json_error(array('message' => 'パラメータが不正です。'));
    }

    $file = $_FILES['pdf_file'];
    $password = sanitize_text_field($_POST['password']);

    // 检查文件类型
    if ($file['type'] !== 'application/pdf') {
        wp_send_json_error(array('message' => 'PDFファイルのみアップロード可能です。'));
    }

    // 上传文件
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $upload = wp_handle_upload($file, array('test_form' => false));

    if (isset($upload['error'])) {
        wp_send_json_error(array('message' => $upload['error']));
    }

    // 保存到媒体库
    $attachment = array(
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($file['name']),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $upload['file']);

    // 更新密码设置
    $passwords = get_option('pdf_download_passwords', array());
    $passwords[$upload['url']] = array(
        'password' => $password
    );
    update_option('pdf_download_passwords', $passwords);

    wp_send_json_success(array(
        'message' => 'アップロード成功',
        'url' => $upload['url']
    ));
}
add_action('wp_ajax_upload_pdf_file', 'handle_pdf_upload');

// 修改短代码处理函数
function protected_pdf_shortcode($atts) {
    $atts = shortcode_atts(array(
        'url' => '',
        'text' => 'ダウンロード'
    ), $atts);
    
    // 从设置中获取密码
    $passwords = get_option('pdf_download_passwords', array());
    $password = isset($passwords[$atts['url']]['password']) ? $passwords[$atts['url']]['password'] : '';
    
    if (empty($password)) {
        return '<p style="color: red;">エラー：PDFファイルのパスワードが設定されていません。</p>';
    }
    
    $unique_id = uniqid('pdf_');
    
    return sprintf(
        '<div class="protected-pdf-download">
            <a href="#" class="pdf-download-link" 
               data-pdf="%s" 
               data-password="%s" 
               data-id="%s">%s</a>
            <div id="%s" class="password-modal" style="display:none;">
                <div class="modal-content">
                    <h5 style="margin-top: 0;">パスワードを入力</h5>
                    <input id="pdf-password" type="password" class="pdf-password" placeholder="パスワードを入力してください">
                    <div class="button-group">
                        <button class="submit-password">確認</button>
                        <button class="close-modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>',
        esc_url($atts['url']),
        esc_attr($password),
        esc_attr($unique_id),
        esc_html($atts['text']),
        esc_attr($unique_id)
    );
}

// AJAX 处理函数
function verify_pdf_password() {
    $submitted_password = $_POST['password'];
    $correct_password = $_POST['correct_password'];
    $pdf_url = $_POST['pdf_url'];
    
    if ($submitted_password === $correct_password) {
        wp_send_json_success(array('url' => $pdf_url));
    } else {
        wp_send_json_error(array('message' => 'パスワードが正しくありません。'));
    }
}
add_action('wp_ajax_verify_pdf_password', 'verify_pdf_password');
add_action('wp_ajax_nopriv_verify_pdf_password', 'verify_pdf_password');

// 注册 Gutenberg 区块
function register_protected_pdf_block() {
    // 移除仅管理员判断，让区块在前台也能正常渲染
    // if (!is_admin()) {
    //     return;
    // }

    // 注册区块样式
    wp_register_style(
        'protected-pdf-block-editor',
        plugins_url('css/pdf-block-editor.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/pdf-block-editor.css')
    );

    // 注册区块脚本
    wp_register_script(
        'protected-pdf-block',
        plugins_url('js/pdf-block.js', __FILE__),
        array(
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-editor',
            'wp-components'
        ),
        filemtime(plugin_dir_path(__FILE__) . 'js/pdf-block.js')
    );

    // 传递已上传的 PDF 列表到区块编辑器
    $passwords = get_option('pdf_download_passwords', array());
    $pdf_list = array();
    foreach ($passwords as $url => $data) {
        $pdf_list[] = array(
            'url' => $url,
            'filename' => basename($url)
        );
    }

    wp_localize_script('protected-pdf-block', 'pdfBlockData', array(
        'pdfList' => $pdf_list
    ));

    // 修改区块注册，添加前台样式
    register_block_type('protected-pdf/download-block', array(
        'editor_script' => 'protected-pdf-block',
        'editor_style' => 'protected-pdf-block-editor',
        'style' => 'pdf-download-css',  // 添加前台样式
        'script' => 'pdf-download-js',   // 添加前台脚本
        'attributes' => array(
            'pdfUrl' => array(
                'type' => 'string'
            ),
            'buttonText' => array(
                'type' => 'string',
                'default' => 'ダウンロード'
            )
        ),
        'render_callback' => function($attributes) {
            if (empty($attributes['pdfUrl'])) {
                return '';
            }

            // 从设置中获取密码
            $passwords = get_option('pdf_download_passwords', array());
            $password = isset($passwords[$attributes['pdfUrl']]['password']) ? $passwords[$attributes['pdfUrl']]['password'] : '';
            
            if (empty($password)) {
                return '<p style="color: red;">エラー：PDFファイルのパスワードが設定されていません。</p>';
            }
            
            $unique_id = uniqid('pdf_');
            $token = generate_pdf_token($attributes['pdfUrl'], $password);
            
            // 确保必要的脚本和样式被加载
            wp_enqueue_script('pdf-download-js');
            wp_enqueue_style('pdf-download-css');
            wp_enqueue_script('jquery');
            
            return sprintf(
                '<div class="protected-pdf-download">
                    <a href="#" class="pdf-download-link" 
                       data-token="%s" 
                       data-id="%s"><span>%s</span><span class="dashicons dashicons-download"></span></a>
                    <div id="%s" class="password-modal">
                        <div class="modal-content">
                            <h5 style="margin-top: 0;">パスワードを入力</h5>
                            <input id="pdf-password" type="password" class="pdf-password" placeholder="パスワードを入力してください">
                            <div class="button-group">
                                <button class="submit-password">確認</button>
                                <button class="close-modal">閉じる</button>
                            </div>
                        </div>
                    </div>
                </div>',
                esc_attr($token),
                esc_attr($unique_id),
                esc_html($attributes['buttonText']),
                esc_attr($unique_id)
            );
        }
    ));
}
add_action('init', 'register_protected_pdf_block');

// 添加一个新函数用于生成和存储临时令牌
function generate_pdf_token($pdf_url, $password) {
    $token = wp_generate_password(32, false); // 生成32位随机字符串
    set_transient('pdf_token_' . $token, array(
        'pdf_url' => $pdf_url,
        'password' => $password
    ), 3600); // 1小时过期
    return $token;
}
