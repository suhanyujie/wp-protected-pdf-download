jQuery(document).ready(function ($) {
  // 使用事件委托绑定点击事件
  $(document).on('click', '.pdf-download-link', function (e) {
    e.preventDefault();
    var modalId = $(this).data('id');
    $('#' + modalId).show();
  });

  // 点击关闭按钮
  $(document).on('click', '.close-modal', function () {
    $(this).closest('.pdf-modal').hide();
  });

  // 点击确认按钮
  $(document).on('click', '.submit-password', function () {
    var modal = $(this).closest('.pdf-modal');
    var link = $('.pdf-download-link[data-id="' + modal.attr('id') + '"]');
    var password = modal.find('.pdf-password').val();

    $.ajax({
      url: pdfAjax.ajaxurl,
      type: 'POST',
      data: {
        action: 'verify_pdf_password',
        password: password,
        pdf_id: link.data('pdf-id'),
      },
      success: function (response) {
        if (response.success) {
          window.location.href = response.data.url;
          modal.hide();
        } else {
          alert(response.data.message);
        }
      },
      error: function () {
        alert('エラーが発生しました。');
      },
    });
  });

  // 添加按下回车键提交密码的功能
  $(document).on('keypress', '.pdf-password', function (e) {
    if (e.which === 13) {
      $(this).closest('.pdf-modal-content').find('.submit-password').click();
    }
  });
});
