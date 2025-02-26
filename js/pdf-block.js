(function(blocks, element, components, i18n) {
    var el = element.createElement;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var __ = i18n.__;

    blocks.registerBlockType('protected-pdf/download-block', {
        title: 'パスワード付きPDF',
        icon: 'pdf',
        category: 'common',
        attributes: {
            pdfUrl: {
                type: 'string'
            },
            buttonText: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            // PDF 选项列表
            var pdfOptions = [
                { label: 'PDFを選択', value: '' }
            ];

            if (window.pdfBlockData && window.pdfBlockData.pdfList) {
                pdfBlockData.pdfList.forEach(function(pdf) {
                    // 限制文件名长度为30个字符
                    var displayName = pdf.filename;
                    if (displayName.length > 30) {
                        displayName = displayName.substring(0, 27) + '...';
                    }
                    pdfOptions.push({
                        label: displayName,
                        value: pdf.url
                    });
                });
            }

            // 当选择PDF文件时，自动设置按钮文字为文件名
            function onPdfSelect(value) {
                setAttributes({ pdfUrl: value });
                if (value) {
                    var selectedPdf = pdfBlockData.pdfList.find(function(pdf) {
                        return pdf.url === value;
                    });
                    if (selectedPdf) {
                        // 移除.pdf扩展名并限制长度
                        var buttonText = selectedPdf.filename.replace(/\.pdf$/i, '');
                        if (buttonText.length > 30) {
                            buttonText = buttonText.substring(0, 27) + '...';
                        }
                        setAttributes({ buttonText: buttonText });
                    }
                }
            }

            return el('div', { className: 'protected-pdf-block-editor' },
                el('div', {
                    style: {
                        padding: '20px',
                        backgroundColor: '#f5f5f5',
                        border: '1px solid #ddd'
                    }
                },
                    el(SelectControl, {
                        label: 'PDF ファイル',
                        value: attributes.pdfUrl,
                        options: pdfOptions,
                        onChange: onPdfSelect
                    }),
                    el(TextControl, {
                        label: 'ボタンテキスト',
                        value: attributes.buttonText,
                        onChange: function(value) {
                            setAttributes({ buttonText: value });
                        }
                    }),
                    !attributes.pdfUrl && el('p', {
                        style: {
                            color: '#666'
                        }
                    }, '※ PDFファイルを選択してください。新しいPDFは「PDF ダウンロード管理」で追加できます。')
                )
            );
        },

        save: function(props) {
            // 返回 null 让 WordPress 使用 render_callback
            return null;
        }
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.i18n
)); 