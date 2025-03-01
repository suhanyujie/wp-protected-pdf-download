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
                    <button class="button edit-pdf" 
                            data-id="<?php echo esc_attr($pdf->id); ?>"
                            onclick="openEditDialog(this)">編集</button>
                    <button class="button delete-pdf" 
                            data-id="<?php echo esc_attr($pdf->id); ?>">削除</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table> 