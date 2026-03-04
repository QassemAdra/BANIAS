<?php
namespace QA\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {

    public function register(): void {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'admin_head',            [ $this, 'inline_styles' ] );
    }

    public function enqueue( string $hook ): void {
        $cpt_screens = [ 'qa_evidence', 'qa_event', 'qa_location' ];
        $post_type   = get_post_type( get_the_ID() ) ?: ( $_GET['post_type'] ?? '' );

        // Always load on QA admin pages
        $is_qa_page = in_array( $post_type, $cpt_screens, true )
            || strpos( $hook, 'qassem' ) !== false
            || strpos( $hook, 'qa-settings' ) !== false
            || in_array( $hook, [ 'post.php', 'post-new.php', 'edit.php' ] );

        if ( ! $is_qa_page ) return;

        // Admin CSS
        wp_enqueue_style(
            'qa-admin',
            QA_PLUGIN_URL . 'admin/css/admin.css',
            [],
            QA_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'qa-admin',
            QA_PLUGIN_URL . 'admin/js/admin.js',
            [ 'jquery' ],
            QA_VERSION,
            true
        );

        // Leaflet for location map preview
        if ( $post_type === 'qa_location' ) {
            wp_enqueue_style(  'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
            wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );
        }

        wp_localize_script( 'qa-admin', 'qaAdmin', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'qa_admin_nonce' ),
            'restUrl'  => rest_url( 'qassem/v1/' ),
            'restNonce' => wp_create_nonce( 'wp_rest' ),
            'postId'   => get_the_ID(),
            'i18n'     => [
                'runAI'         => __( '🤖 تشغيل تحليل AI', QA_TEXT_DOMAIN ),
                'processing'    => __( '⏳ جارٍ التحليل...', QA_TEXT_DOMAIN ),
                'aiReady'       => __( '✅ اكتمل التحليل', QA_TEXT_DOMAIN ),
                'aiError'       => __( '❌ حدث خطأ', QA_TEXT_DOMAIN ),
                'accepted'      => __( 'تم الاعتماد', QA_TEXT_DOMAIN ),
                'dismissed'     => __( 'تم التجاهل', QA_TEXT_DOMAIN ),
                'confirmRun'    => __( 'هل تريد تشغيل تحليل AI على هذا الدليل؟', QA_TEXT_DOMAIN ),

                'selectFile'     => __( 'يرجى اختيار ملف أولاً', QA_TEXT_DOMAIN ),
                'requestingUpload'=> __( 'جارٍ تجهيز رابط الرفع...', QA_TEXT_DOMAIN ),
                'uploading'      => __( 'جارٍ رفع الملف...', QA_TEXT_DOMAIN ),
                'uploadDone'     => __( '✅ تم الرفع', QA_TEXT_DOMAIN ),
                'uploadError'    => __( '❌ فشل الرفع', QA_TEXT_DOMAIN ),

            ],
        ] );
    }

    public function inline_styles(): void {
        // Quick badge styles injected in <head>
        echo '<style>
            .qa-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; }
            .qa-badge-grey   { background:#f0f0f0; color:#555; }
            .qa-badge-yellow { background:#fff3cd; color:#856404; }
            .qa-badge-blue   { background:#d1ecf1; color:#0c5460; }
            .qa-badge-green  { background:#d4edda; color:#155724; }
            .qa-count-badge  { background:#0073aa; color:#fff; border-radius:10px; padding:2px 8px; font-size:11px; }
            .qa-type-badge   { font-size:12px; }
        </style>';
    }
}
