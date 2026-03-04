<?php
namespace QA\Admin;

defined( 'ABSPATH' ) || exit;

class Settings {

    private string $option_key = 'qa_settings';

    public function register(): void {
        add_action( 'admin_menu', [ $this, 'add_menu'    ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'wp_ajax_qa_test_r2',   [ $this, 'ajax_test_r2' ] );
        add_action( 'wp_ajax_qa_test_ai',   [ $this, 'ajax_test_ai' ] );
        add_action( 'wp_ajax_qa_rebuild_index', [ $this, 'ajax_rebuild_index' ] );
    }

    // ─── Menu ─────────────────────────────────────────────────────────────
    public function add_menu(): void {
        // Top-level menu (parent for all CPT sub-items)
        add_menu_page(
            __( 'Qassem Archive', QA_TEXT_DOMAIN ),
            __( 'Qassem Archive', QA_TEXT_DOMAIN ),
            'manage_options',
            'qassem-archive',
            [ $this, 'render_dashboard' ],
            'dashicons-archive',
            20
        );

        add_submenu_page(
            'qassem-archive',
            __( 'الإعدادات', QA_TEXT_DOMAIN ),
            __( 'الإعدادات', QA_TEXT_DOMAIN ),
            'manage_options',
            'qa-settings',
            [ $this, 'render_page' ]
        );
    }

    // ─── Dashboard (quick stats) ──────────────────────────────────────────
    public function render_dashboard(): void {
        $counts = [
            'qa_evidence' => wp_count_posts( 'qa_evidence' ),
            'qa_event'    => wp_count_posts( 'qa_event' ),
            'qa_location' => wp_count_posts( 'qa_location' ),
        ];
        ?>
        <div class="wrap" dir="rtl">
            <h1>🗂️ <?php _e( 'Qassem Archive — لوحة التحكم', QA_TEXT_DOMAIN ); ?></h1>
            <div class="qa-dashboard-cards">
                <div class="qa-dash-card">
                    <span class="dashicons dashicons-media-document"></span>
                    <strong><?php echo intval( $counts['qa_evidence']->publish ?? 0 ); ?></strong>
                    <span><?php _e( 'دليل منشور', QA_TEXT_DOMAIN ); ?></span>
                    <small><?php echo intval( $counts['qa_evidence']->draft ?? 0 ); ?> <?php _e( 'مسودة', QA_TEXT_DOMAIN ); ?></small>
                </div>
                <div class="qa-dash-card">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <strong><?php echo intval( $counts['qa_event']->publish ?? 0 ); ?></strong>
                    <span><?php _e( 'حدث', QA_TEXT_DOMAIN ); ?></span>
                </div>
                <div class="qa-dash-card">
                    <span class="dashicons dashicons-location"></span>
                    <strong><?php echo intval( $counts['qa_location']->publish ?? 0 ); ?></strong>
                    <span><?php _e( 'موقع', QA_TEXT_DOMAIN ); ?></span>
                </div>
            </div>
            <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=qa-settings' ) ); ?>" class="button button-primary">
                ⚙️ <?php _e( 'الإعدادات', QA_TEXT_DOMAIN ); ?>
            </a></p>
        </div>
        <?php
    }

    // ─── Settings registration ────────────────────────────────────────────
    public function register_settings(): void {
        register_setting( 'qa_settings_group', $this->option_key, [
            'sanitize_callback' => [ $this, 'sanitize_settings' ],
        ] );

        // R2 Section
        add_settings_section( 'qa_r2_section', __( 'إعدادات Cloudflare R2', QA_TEXT_DOMAIN ), null, 'qa-settings' );
        $r2_fields = [
            'r2_account_id'  => __( 'Account ID', QA_TEXT_DOMAIN ),
            'r2_access_key'  => __( 'Access Key ID', QA_TEXT_DOMAIN ),
            'r2_secret_key'  => __( 'Secret Access Key', QA_TEXT_DOMAIN ),
            'r2_bucket_name' => __( 'Bucket Name', QA_TEXT_DOMAIN ),
            'r2_public_url'  => __( 'Public URL (Custom Domain)', QA_TEXT_DOMAIN ),
        ];
        foreach ( $r2_fields as $key => $label ) {
            add_settings_field( "qa_{$key}", $label, [ $this, 'render_text_field' ], 'qa-settings', 'qa_r2_section', [
                'key'      => $key,
                'type'     => in_array( $key, [ 'r2_secret_key' ] ) ? 'password' : 'text',
                'required' => false,
            ] );
        }

        // AI Section
        add_settings_section( 'qa_ai_section', __( 'إعدادات AI', QA_TEXT_DOMAIN ), [ $this, 'render_ai_section_desc' ], 'qa-settings' );
        add_settings_field( 'qa_ai_provider', __( 'مزود AI', QA_TEXT_DOMAIN ), [ $this, 'render_provider_field' ], 'qa-settings', 'qa_ai_section' );
        add_settings_field( 'qa_ai_api_key', __( 'API Key', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_ai_section', [
            'key' => 'ai_api_key', 'type' => 'password',
        ] );
        add_settings_field( 'qa_ai_model', __( 'النموذج', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_ai_section', [
            'key' => 'ai_model', 'type' => 'text',
        ] );

        add_settings_field( 'qa_ai_api_base', __( 'API Base URL (اختياري)', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_ai_section', [
            'key' => 'ai_api_base', 'type' => 'text',
        ] );
        add_settings_field( 'qa_ai_custom_url', __( 'Custom AI URL (للـ Provider=Custom)', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_ai_section', [
            'key' => 'ai_custom_url', 'type' => 'text',
        ] );

        // Map Section
        add_settings_section( 'qa_map_section', __( 'إعدادات الخريطة الافتراضية', QA_TEXT_DOMAIN ), null, 'qa-settings' );
        add_settings_field( 'qa_map_lat', __( 'خط العرض الافتراضي', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_map_section', [
            'key' => 'default_map_lat', 'type' => 'text',
        ] );
        add_settings_field( 'qa_map_lng', __( 'خط الطول الافتراضي', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_map_section', [
            'key' => 'default_map_lng', 'type' => 'text',
        ] );
        add_settings_field( 'qa_map_zoom', __( 'مستوى التكبير الافتراضي', QA_TEXT_DOMAIN ), [ $this, 'render_text_field' ], 'qa-settings', 'qa_map_section', [
            'key' => 'default_map_zoom', 'type' => 'number',
        ] );
    }

    // ─── Settings Page Render ─────────────────────────────────────────────
    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'غير مصرح', QA_TEXT_DOMAIN ) );
        ?>
        <div class="wrap" dir="rtl">
            <h1>⚙️ <?php _e( 'إعدادات Qassem Archive', QA_TEXT_DOMAIN ); ?></h1>
            <?php settings_errors( 'qa_settings_group' ); ?>

            <nav class="qa-settings-tabs">
                <a href="#qa-r2-section" class="qa-settings-tab active"><?php _e( '☁️ Cloudflare R2', QA_TEXT_DOMAIN ); ?></a>
                <a href="#qa-ai-section" class="qa-settings-tab"><?php _e( '🤖 AI', QA_TEXT_DOMAIN ); ?></a>
                <a href="#qa-map-section" class="qa-settings-tab"><?php _e( '🗺️ الخريطة', QA_TEXT_DOMAIN ); ?></a>
            </nav>

            <form method="post" action="options.php">
                <?php settings_fields( 'qa_settings_group' ); ?>
                <?php do_settings_sections( 'qa-settings' ); ?>

                <div class="qa-settings-actions">
                    <?php submit_button( __( 'حفظ الإعدادات', QA_TEXT_DOMAIN ), 'primary', 'submit', false ); ?>
                    <button type="button" id="qa-test-r2" class="button">
                        <?php _e( 'اختبار اتصال R2', QA_TEXT_DOMAIN ); ?>
                    </button>
                    <button type="button" id="qa-test-ai" class="button">
                        <?php _e( 'اختبار اتصال AI', QA_TEXT_DOMAIN ); ?>
                    </button>
                    <button type="button" id="qa-rebuild-index" class="button">
                        🔄 <?php _e( 'إعادة بناء فهرس البحث', QA_TEXT_DOMAIN ); ?>
                    </button>
                    <span id="qa-test-result" class="qa-test-result"></span>
                </div>
            </form>
        </div>
        <?php
    }

    // ─── Field Renderers ──────────────────────────────────────────────────
    public function render_text_field( array $args ): void {
        $settings = get_option( $this->option_key, [] );
        $key  = $args['key'];
        $type = $args['type'] ?? 'text';
        $val  = $settings[ $key ] ?? '';
        printf(
            '<input type="%s" name="%s[%s]" value="%s" class="regular-text" autocomplete="off">',
            esc_attr( $type ),
            esc_attr( $this->option_key ),
            esc_attr( $key ),
            esc_attr( $type === 'password' && $val ? '**stored**' : $val )
        );
    }

    public function render_provider_field(): void {
        $settings = get_option( $this->option_key, [] );
        $current  = $settings['ai_provider'] ?? 'none';
        $options  = [
            'none'      => 'Disabled',
            'openai'    => 'OpenAI',
            'anthropic' => 'Anthropic (Claude)',
            'custom'    => 'Custom Endpoint',
        ];
        echo '<select name="' . esc_attr( $this->option_key ) . '[ai_provider]" class="regular-text">';
        foreach ( $options as $val => $label ) {
            printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $current, $val, false ), esc_html( $label ) );
        }
        echo '</select>';
    }

    public function render_ai_section_desc(): void {
        echo '<p class="description">' . __( 'مفاتيح API مشفرة ومخزنة بأمان. AI يُستخدم فقط من Server-side.', QA_TEXT_DOMAIN ) . '</p>';
    }

    // ─── Sanitize ─────────────────────────────────────────────────────────
    public function sanitize_settings( $input ): array {
        $old      = get_option( $this->option_key, [] );
        $clean    = [];
        $text_fields = [ 'r2_account_id', 'r2_bucket_name', 'r2_public_url', 'ai_model', 'ai_provider', 'ai_api_base', 'ai_custom_url',
                         'default_map_lat', 'default_map_lng', 'default_map_zoom' ];
        $secret_fields = [ 'r2_access_key', 'r2_secret_key', 'ai_api_key' ];

        foreach ( $text_fields as $key ) {
            $clean[ $key ] = sanitize_text_field( $input[ $key ] ?? '' );
        }
        foreach ( $secret_fields as $key ) {
            // If user submitted placeholder, keep old value
            $val = $input[ $key ] ?? '';
            $clean[ $key ] = ( $val === '**stored**' || $val === '' ) ? ( $old[ $key ] ?? '' ) : sanitize_text_field( $val );
        }
        return $clean;
    }

    // ─── AJAX test handlers ───────────────────────────────────────────────
    public function ajax_test_r2(): void {
        check_ajax_referer( 'qa_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        // TODO: Implement actual R2 connection test in Phase 3
        wp_send_json_success( [ 'message' => __( 'اختبار R2 سيُفعَّل في المرحلة التالية', QA_TEXT_DOMAIN ) ] );
    }

    public function ajax_test_ai(): void {
        check_ajax_referer( 'qa_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        // TODO: Implement actual AI connection test in Phase 3
        wp_send_json_success( [ 'message' => __( 'اختبار AI سيُفعَّل في المرحلة التالية', QA_TEXT_DOMAIN ) ] );
    }

    // ─── Rebuild Index AJAX ───────────────────────────────────────────────
    public function ajax_rebuild_index(): void {
        check_ajax_referer( 'qa_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        $count = \QA\QueryOptimizer::rebuild_index();
        wp_send_json_success( [ 'message' => sprintf( __( 'تم إعادة بناء الفهرس لـ %d دليل', QA_TEXT_DOMAIN ), $count ) ] );
    }
}
// NOTE: Added below to existing class via include at runtime
