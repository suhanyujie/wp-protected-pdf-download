<?php
$pdf_model = new PDF_Model();
$pdfs = $pdf_model->get_all_pdfs();
?>
<div class="wrap">
    <h1>PDF ダウンロードパスワード管理</h1>
    
    <!-- PDF上传表单 -->
    <div class="pdf-upload-section">
        <h2>新しいPDFを追加</h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_pdf">
            <?php wp_nonce_field('upload_pdf_action', 'pdf_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="pdf_file">PDFファイル</label></th>
                    <td>
                        <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" required>
                        <p class="description">アップロード可能なファイル: PDF</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="pdf_password">パスワード</label></th>
                    <td>
                        <input type="text" name="pdf_password" id="pdf_password" required>
                        <p class="description">ダウンロード時に必要なパスワードを設定してください</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('アップロード', 'primary', 'submit'); ?>
        </form>
    </div>

    <!-- PDF列表 -->
    <h2>登録済みPDF一覧</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ファイル名</th>
                <th>パスワード</th>
                <th>ダウンロード数</th>
                <th>作成日時</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pdfs)): ?>
            <tr>
                <td colspan="5">PDFファイルがまだ登録されていません。</td>
            </tr>
            <?php else: ?>
                <?php foreach ($pdfs as $pdf): ?>
                <tr>
                    <td><?php echo esc_html($pdf->file_name); ?></td>
                    <td>
                        <div class="password-field">
                            <input type="password" 
                                   value="********" 
                                   readonly 
                                   style="background-color: #f5f5f5;">
                            <button class="button reset-password" 
                                    data-id="<?php echo esc_attr($pdf->id); ?>"
                                    title="パスワードをリセット">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                    </td>
                    <td><?php echo esc_html($pdf->downloads); ?></td>
                    <td><?php echo esc_html($pdf->created_at); ?></td>
                    <td>
                        <button class="button edit-pdf" data-id="<?php echo esc_attr($pdf->id); ?>" data-password="<?php echo esc_attr($pdf->password); ?>" onclick="openEditDialog(this)">編集</button>
                        <button class="button delete-pdf" data-id="<?php echo esc_attr($pdf->id); ?>">削除</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 在文件末尾添加编辑对话框 -->
<div id="edit-pdf-dialog" class="pdf-edit-modal" style="display:none;">
    <div class="pdf-edit-modal-content">
        <h3>PDFファイルを編集</h3>
        <form id="edit-pdf-form">
            <input type="hidden" id="edit-pdf-id" name="pdf_id">
            <table class="form-table">
                <tr>
                    <th><label for="edit-file-name">ファイル名</label></th>
                    <td>
                        <input type="text" id="edit-file-name" name="file_name" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="edit-password">新しいパスワード</label></th>
                    <td>
                        <input type="text" id="edit-password" name="password" class="regular-text">
                        <p class="description">変更しない場合は空白のままにしてください</p>
                    </td>
                </tr>
            </table>
            <div class="button-group">
                <button type="submit" class="button button-primary">保存</button>
                <button type="button" class="button" onclick="closeEditDialog()">キャンセル</button>
            </div>
        </form>
    </div>
</div> 