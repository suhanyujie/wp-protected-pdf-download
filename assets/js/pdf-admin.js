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
}); 