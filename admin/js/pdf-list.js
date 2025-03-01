jQuery(document).ready(function($) {
    // 删除PDF
    $('.delete-pdf').click(function() {
        if (!confirm('PDFファイルを削除しますか？')) {
            return;
        }

        var button = $(this);
        var pdfId = button.data('id');

        $.ajax({
            url: pdfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_pdf',
                pdf_id: pdfId,
                security: pdfAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            $('tbody').append('<tr><td colspan="5">PDFファイルがまだ登録されていません。</td></tr>');
                        }
                    });
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('エラーが発生しました。');
            }
        });
    });

    // 重置密码
    $('.reset-password').click(function() {
        if (!confirm('パスワードをリセットしますか？')) {
            return;
        }

        var button = $(this);
        var pdfId = button.data('id');

        $.ajax({
            url: pdfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'reset_pdf_password',
                pdf_id: pdfId,
                security: pdfAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('新しいパスワード: ' + response.data.new_password);
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('エラーが発生しました。');
            }
        });
    });
}); 