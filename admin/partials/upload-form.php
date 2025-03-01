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