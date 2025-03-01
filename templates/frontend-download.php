<div class="protected-pdf-download">
    <a href="#" class="pdf-download-link" 
       data-pdf-id="<?php echo esc_attr($attributes['pdfId']); ?>" 
       data-id="<?php echo esc_attr($unique_id); ?>">
        <span class="dashicons dashicons-pdf"></span>
        <span class="button-text"><?php echo esc_html($attributes['buttonText']); ?></span>
        <span class="dashicons dashicons-download"></span>
    </a>
</div>

<!-- 修改类名避免冲突 -->
<div id="<?php echo esc_attr($unique_id); ?>" class="pdf-modal">
    <div class="pdf-modal-dialog">
        <div class="pdf-modal-content">
            <h5>パスワードを入力</h5>
            <input type="password" 
                   class="pdf-password" 
                   placeholder="パスワードを入力してください"
                   autocomplete="off">
            <div class="button-group">
                <button class="submit-password">確認</button>
                <button class="close-modal">閉じる</button>
            </div>
        </div>
    </div>
</div> 