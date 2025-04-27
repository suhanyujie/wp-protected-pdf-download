<div class="pdf-upload-section">
    <h2>パスワードつける PDF ファイルを追加</h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_pdf">
        <?php wp_nonce_field('upload_pdf_action', 'pdf_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="pdf_file">PDFファイル</label></th>
                <td>
                   <div class="pdf-upload-methods">
                        <div class="upload-method">
                            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf">
                            <p class="description">アップロード可能なファイル: PDF</p>
                        </div>
                        <div class="upload-method">
                            <p>または</p>
                            <button type="button" class="button" id="media-upload-btn">
                                メディアライブラリから選択
                            </button>
                            <input type="hidden" name="media_pdf_id" id="media_pdf_id" value="">
                            <div id="selected-pdf-info"></div>
                        </div>
                    </div>
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

<style>
.pdf-upload-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.upload-method {
    padding: 10px;
    border: 1px dashed #ccc;
    background: #f9f9f9;
}
#selected-pdf-info {
    margin-top: 10px;
    font-style: italic;
}
</style>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;

    $('#media-upload-btn').click(function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'PDFファイルを選択',
            button: {
                text: '選択'
            },
            multiple: false,
            library: {
                type: 'application/pdf'
            }
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#media_pdf_id').val(attachment.id);
            $('#selected-pdf-info').html('選択したファイル: ' + attachment.filename);
            $('#pdf_file').val('');
        });

        mediaUploader.open();
    });

    $('#pdf_file').on('change', function() {
        if ($(this).val()) {
            $('#media_pdf_id').val('');
            $('#selected-pdf-info').html('');
        }
    });
});
</script>
