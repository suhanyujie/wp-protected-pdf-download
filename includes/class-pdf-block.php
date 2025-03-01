<?php
class PDF_Block {
    private $pdf_model;

    public function __construct() {
        $this->pdf_model = new PDF_Model();
        add_action('init', array($this, 'register_block'));
    }

    public function register_block() {
        wp_register_script(
            'protected-pdf-block',
            PDF_PLUGIN_URL . '/assets/js/pdf-block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
        );

        wp_register_style(
            'protected-pdf-block-editor',
            PDF_PLUGIN_URL . '/assets/css/pdf-block.css',
            array(),
            PDF_VERSION
        );

        $this->localize_block_data();

        register_block_type('protected-pdf/download-block', array(
            'editor_script' => 'protected-pdf-block',
            'editor_style' => 'protected-pdf-block-editor',
            'attributes' => array(
                'pdfId' => array(
                    'type' => 'number'
                ),
                'buttonText' => array(
                    'type' => 'string',
                    'default' => ''
                )
            ),
            'render_callback' => array($this, 'render_block')
        ));
    }

    private function localize_block_data() {
        $pdfs = $this->pdf_model->get_all_pdfs();
        $pdf_list = array_map(function($pdf) {
            return array(
                'id' => intval($pdf->id),
                'filename' => $pdf->file_name,
                'url' => $pdf->file_url
            );
        }, $pdfs);

        wp_localize_script('protected-pdf-block', 'pdfBlockData', array(
            'pdfList' => $pdf_list
        ));
    }

    public function render_block($attributes) {
        if (empty($attributes['pdfId'])) {
            return '';
        }

        $pdf = $this->pdf_model->get_pdf($attributes['pdfId']);
        if (!$pdf) {
            return '';
        }

        $attributes['buttonText'] = !empty($attributes['buttonText']) ? $attributes['buttonText'] : $pdf->file_name;

        return PDF_Frontend::render_download_link($attributes);
    }
} 