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