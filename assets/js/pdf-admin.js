jQuery(document).ready(function($) {
    $('.reset-password').click(function() {
        if (!confirm('パスワードをリセットしますか？')) {
            return;
        }

        var button = $(this);
        var pdfId = button.data('id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reset_pdf_password',
                pdf_id: pdfId
            },
            success: function(response) {
                if (response.success) {
                    alert('新しいパスワード: ' + response.data.new_password);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // 打开编辑对话框
    window.openEditDialog = function(button) {
        var $button = $(button);
        var $row = $button.closest('tr');
        var pdfId = $button.data('id');
        
        $('#edit-pdf-id').val(pdfId);
        $('#edit-file-name').val($row.find('td:first').text());
        $('#edit-password').val('');
        
        $('#edit-pdf-dialog').show();
    };

    // 关闭编辑对话框
    window.closeEditDialog = function() {
        $('#edit-pdf-dialog').hide();
        $('#edit-pdf-form')[0].reset();
    };

    // 处理编辑表单提交
    $('#edit-pdf-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'edit_pdf',
            pdf_id: $('#edit-pdf-id').val(),
            file_name: $('#edit-file-name').val(),
            password: $('#edit-password').val(),
            security: pdfAjax.nonce
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('更新が完了しました。');
                    location.reload();
                } else {
                    alert(response.data.message || 'エラーが発生しました。');
                }
            },
            error: function() {
                alert('エラーが発生しました。');
            }
        });
    });

    // 点击空白处关闭对话框
    $(window).click(function(e) {
        if ($(e.target).hasClass('pdf-edit-modal')) {
            closeEditDialog();
        }
    });
}); 