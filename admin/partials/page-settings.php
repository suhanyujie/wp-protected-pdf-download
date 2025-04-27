<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>パスワードつける PDF ダウンロード管理</h1>

    <?php
    // 显示上传成功消息
    if (isset($_GET['uploaded']) && $_GET['uploaded'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p>PDFファイルが正常にアップロードされました。</p></div>';
    }
    ?>

    <!-- 上传表单部分 -->
    <?php $this->upload_handler->render_upload_form(); ?>

    <!-- PDF列表部分 -->
    <?php $this->list_handler->render_list(); ?>

    <!-- 编辑模态框 -->
    <?php $this->edit_handler->render_edit_modal(); ?>
</div>
