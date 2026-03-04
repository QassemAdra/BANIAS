<?php
namespace QA\Admin;

defined( 'ABSPATH' ) || exit;

use QA\MetaFields;

class MetaBoxes {

    public function register(): void {
        add_action( 'add_meta_boxes', [ $this, 'add_boxes' ] );
        add_action( 'save_post',      [ $this, 'save'      ], 10, 2 );
    }

    // ─── Register meta boxes ──────────────────────────────────────────────
    public function add_boxes(): void {
        // Evidence — tabbed meta box
        add_meta_box(
            'qa_evidence_details',
            __( 'تفاصيل الدليل', QA_TEXT_DOMAIN ),
            [ $this, 'render_evidence_box' ],
            'qa_evidence',
            'normal',
            'high'
        );

        // Event details
        add_meta_box(
            'qa_event_details',
            __( 'تفاصيل الحدث', QA_TEXT_DOMAIN ),
            [ $this, 'render_event_box' ],
            'qa_event',
            'normal',
            'high'
        );

        // Location details
        add_meta_box(
            'qa_location_details',
            __( 'تفاصيل الموقع', QA_TEXT_DOMAIN ),
            [ $this, 'render_location_box' ],
            'qa_location',
            'normal',
            'high'
        );
    }

    // ─── Evidence Meta Box (Tabbed) ───────────────────────────────────────
    public function render_evidence_box( \WP_Post $post ): void {
        wp_nonce_field( 'qa_evidence_save', 'qa_evidence_nonce' );

        $schema = MetaFields::schema()['qa_evidence'];
        $sections = $this->group_by_section( $schema, $post->ID );

        $tabs = [
            'core'         => [ 'icon' => '📋', 'label' => __( 'البيانات الأساسية', QA_TEXT_DOMAIN ) ],
            'media'        => [ 'icon' => '🎬', 'label' => __( 'الوسائط', QA_TEXT_DOMAIN ) ],
            'verification' => [ 'icon' => '✅', 'label' => __( 'التحقق', QA_TEXT_DOMAIN ) ],
            'ai'           => [ 'icon' => '🤖', 'label' => __( 'تحليل AI', QA_TEXT_DOMAIN ) ],
        ];

        ?>
        <div class="qa-metabox-tabs" dir="rtl">
            <nav class="qa-tabs-nav">
                <?php foreach ( $tabs as $tab_key => $tab ) : ?>
                    <button type="button" class="qa-tab-btn <?php echo $tab_key === 'core' ? 'active' : ''; ?>"
                            data-tab="<?php echo esc_attr( $tab_key ); ?>">
                        <?php echo $tab['icon']; ?> <?php echo esc_html( $tab['label'] ); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <?php foreach ( $tabs as $tab_key => $tab ) : ?>
                <div class="qa-tab-panel <?php echo $tab_key === 'core' ? 'active' : ''; ?>"
                     data-panel="<?php echo esc_attr( $tab_key ); ?>">

                    <?php if ( $tab_key === 'ai' ) : ?>
                        <?php $this->render_ai_panel( $post, $sections['ai'] ?? [] ); ?>
                    <?php else : ?>
                        <table class="form-table qa-form-table">
                            <?php foreach ( ( $sections[ $tab_key ] ?? [] ) as $meta_key => $field ) : ?>
                                <tr>
                                    <th scope="row">
                                        <label for="<?php echo esc_attr( $meta_key ); ?>">
                                            <?php echo esc_html( $field['label'] ); ?>
                                            <?php if ( ! empty( $field['required'] ) ) : ?>
                                                <span class="required">*</span>
                                            <?php endif; ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php $this->render_field( $meta_key, $field, $post->ID ); ?>
                                        <?php if ( ! empty( $field['description'] ) ) : ?>
                                            <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    // ─── AI Panel ─────────────────────────────────────────────────────────
    private function render_ai_panel( \WP_Post $post, array $fields ): void {
        $ai_status  = get_post_meta( $post->ID, 'qa_ai_status', true ) ?: 'idle';
        $last_run   = get_post_meta( $post->ID, 'qa_ai_last_run', true );
        $error_msg  = get_post_meta( $post->ID, 'qa_ai_error_message', true );
        $status_map = [
            'idle'       => [ 'label' => __( 'في الانتظار', QA_TEXT_DOMAIN ),      'class' => 'qa-status-idle' ],
            'processing' => [ 'label' => __( 'جارٍ التحليل...', QA_TEXT_DOMAIN ),  'class' => 'qa-status-processing' ],
            'ready'      => [ 'label' => __( 'مكتمل', QA_TEXT_DOMAIN ),             'class' => 'qa-status-ready' ],
            'error'      => [ 'label' => __( 'خطأ', QA_TEXT_DOMAIN ),               'class' => 'qa-status-error' ],
        ];
        ?>
        <div class="qa-ai-panel">
            <div class="qa-ai-header">
                <div class="qa-ai-status <?php echo esc_attr( $status_map[ $ai_status ]['class'] ?? '' ); ?>">
                    <?php echo esc_html( $status_map[ $ai_status ]['label'] ?? $ai_status ); ?>
                </div>
                <?php if ( $last_run ) : ?>
                    <span class="qa-ai-last-run">
                        <?php printf( __( 'آخر تشغيل: %s', QA_TEXT_DOMAIN ), esc_html( $last_run ) ); ?>
                    </span>
                <?php endif; ?>
                <button type="button" id="qa-run-ai-btn"
                        class="button button-primary"
                        data-post-id="<?php echo esc_attr( $post->ID ); ?>"
                        <?php disabled( $ai_status, 'processing' ); ?>>
                    🤖 <?php _e( 'تشغيل تحليل AI', QA_TEXT_DOMAIN ); ?>
                </button>
            </div>

            <?php if ( $ai_status === 'error' && $error_msg ) : ?>
                <div class="notice notice-error inline">
                    <p><?php echo esc_html( $error_msg ); ?></p>
                </div>
            <?php endif; ?>

            <div class="qa-ai-notice">
                <span class="dashicons dashicons-info"></span>
                <?php _e(
                    'تنبيه: نتائج AI هي اقتراحات فقط وتحتاج إلى مراجعة بشرية. AI لا يحدد هويات أشخاص.',
                    QA_TEXT_DOMAIN
                ); ?>
            </div>

            <table class="form-table qa-form-table">
                <?php foreach ( $fields as $meta_key => $field ) : ?>
                    <?php if ( in_array( $field['input'], [ 'readonly', 'ai_textarea', 'ai_json' ] ) ) : ?>
                        <tr>
                            <th scope="row">
                                <label><?php echo esc_html( $field['label'] ); ?></label>
                            </th>
                            <td>
                                <?php $this->render_ai_field( $meta_key, $field, $post->ID ); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }

    // ─── Event Meta Box ───────────────────────────────────────────────────
    public function render_event_box( \WP_Post $post ): void {
        wp_nonce_field( 'qa_event_save', 'qa_event_nonce' );
        $schema = MetaFields::schema()['qa_event'];
        ?>
        <div dir="rtl">
            <table class="form-table qa-form-table">
                <?php foreach ( $schema as $meta_key => $field ) : ?>
                    <tr>
                        <th><label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
                        <td>
                            <?php $this->render_field( $meta_key, $field, $post->ID ); ?>
                            <?php if ( ! empty( $field['description'] ) ) : ?>
                                <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
    }

    // ─── Location Meta Box ────────────────────────────────────────────────
    public function render_location_box( \WP_Post $post ): void {
        wp_nonce_field( 'qa_location_save', 'qa_location_nonce' );
        $schema   = MetaFields::schema()['qa_location'];
        $sections = $this->group_by_section( $schema, $post->ID );
        $tabs = [
            'core' => __( 'معلومات أساسية', QA_TEXT_DOMAIN ),
            'geo'  => __( 'الجغرافيا', QA_TEXT_DOMAIN ),
        ];
        ?>
        <div class="qa-metabox-tabs" dir="rtl">
            <nav class="qa-tabs-nav">
                <?php foreach ( $tabs as $key => $label ) : ?>
                    <button type="button" class="qa-tab-btn <?php echo $key === 'core' ? 'active' : ''; ?>"
                            data-tab="<?php echo esc_attr( $key ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </button>
                <?php endforeach; ?>
            </nav>
            <?php foreach ( $tabs as $key => $label ) : ?>
                <div class="qa-tab-panel <?php echo $key === 'core' ? 'active' : ''; ?>"
                     data-panel="<?php echo esc_attr( $key ); ?>">
                    <table class="form-table qa-form-table">
                        <?php foreach ( ( $sections[ $key ] ?? [] ) as $meta_key => $field ) : ?>
                            <tr>
                                <th><label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
                                <td>
                                    <?php $this->render_field( $meta_key, $field, $post->ID ); ?>
                                    <?php if ( ! empty( $field['description'] ) ) : ?>
                                        <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php if ( $key === 'geo' ) : ?>
                        <div id="qa-location-map-preview" style="height:300px; margin:10px 0; border:1px solid #ccc;"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    // ─── Field Renderer ───────────────────────────────────────────────────
    private function render_field( string $meta_key, array $field, int $post_id ): void {
        $value = get_post_meta( $post_id, $meta_key, true );
        if ( $value === '' && isset( $field['default'] ) ) {
            $value = $field['default'];
        }
        $input = $field['input'] ?? 'text';
        $id    = esc_attr( $meta_key );
        $name  = esc_attr( $meta_key );

        switch ( $input ) {
            case 'text':
                printf(
                    '<input type="text" id="%s" name="%s" value="%s" class="regular-text">',
                    $id, $name, esc_attr( $value )
                );
                break;

            case 'url':
                printf(
                    '<input type="url" id="%s" name="%s" value="%s" class="regular-text qa-url-field">',
                    $id, $name, esc_attr( $value )
                );

                // If this is the R2 media URL, show an optional direct-upload helper.
                if ( $meta_key === 'qa_media_url' ) {
                    echo '<div class="qa-r2-upload" style="margin-top:8px;">';
                    echo '<input type="file" class="qa-r2-file" accept="video/*,image/*,application/pdf" />';
                    echo '<button type="button" class="button qa-r2-upload-btn" data-target="#' . $id . '">⬆️ رفع إلى R2</button>';
                    echo '<span class="qa-r2-upload-status" style="margin-right:8px;"></span>';
                    echo '</div>';
                    echo '<p class="description">يمكنك لصق رابط مباشر، أو رفع ملف إلى Cloudflare R2 (يتطلب إعداد R2 في الإعدادات).</p>';
                }

                break;

            case 'date':
                printf(
                    '<input type="date" id="%s" name="%s" value="%s" class="qa-date-field">',
                    $id, $name, esc_attr( $value )
                );
                break;

            case 'textarea':
                printf(
                    '<textarea id="%s" name="%s" rows="%d" class="large-text">%s</textarea>',
                    $id, $name, intval( $field['rows'] ?? 4 ), esc_textarea( $value )
                );
                break;

            case 'select':
                echo '<select id="' . $id . '" name="' . $name . '" class="qa-select">';
                foreach ( $field['options'] as $opt_val => $opt_label ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $opt_val ),
                        selected( $value, $opt_val, false ),
                        esc_html( $opt_label )
                    );
                }
                echo '</select>';
                break;

            case 'post_select':
                $this->render_post_select( $meta_key, $field, $post_id, $value );
                break;

            case 'readonly':
                $display = $value;
                if ( isset( $field['options'][ $value ] ) ) {
                    $display = $field['options'][ $value ];
                }
                printf(
                    '<span class="qa-readonly-field">%s</span>'
                    . '<input type="hidden" name="%s" value="%s">',
                    esc_html( $display ?: '—' ),
                    $name,
                    esc_attr( $value )
                );
                break;

            case 'json_editor':
                printf(
                    '<textarea id="%s" name="%s" rows="6" class="large-text qa-json-editor" '
                    . 'placeholder=\'{"key": "value"}\'>%s</textarea>',
                    $id, $name, esc_textarea( $value )
                );
                break;

            default:
                printf(
                    '<input type="text" id="%s" name="%s" value="%s" class="regular-text">',
                    $id, $name, esc_attr( $value )
                );
        }
    }

    // ─── AI Field Renderer ────────────────────────────────────────────────
    private function render_ai_field( string $meta_key, array $field, int $post_id ): void {
        $value = get_post_meta( $post_id, $meta_key, true );
        $input = $field['input'];

        if ( $input === 'readonly' ) {
            echo '<span class="qa-readonly-field">' . esc_html( $value ?: '—' ) . '</span>';
            return;
        }

        $id   = esc_attr( $meta_key );
        $name = esc_attr( $meta_key );

        if ( $input === 'ai_json' ) {
            echo '<div class="qa-ai-json-field">';
            echo '<textarea id="' . $id . '" name="' . $name . '" rows="4" class="large-text qa-json-editor qa-ai-field">'
                . esc_textarea( $value ) . '</textarea>';
            if ( $value ) {
                $decoded = json_decode( $value, true );
                if ( is_array( $decoded ) ) {
                    echo '<div class="qa-ai-tags-preview">';
                    foreach ( $decoded as $tag ) {
                        echo '<span class="qa-tag-pill">' . esc_html( is_string( $tag ) ? $tag : json_encode( $tag ) ) . '</span>';
                    }
                    echo '</div>';
                }
            }
            echo '<div class="qa-ai-field-actions">';
            printf( '<button type="button" class="button qa-accept-ai" data-field="%s">%s</button>', $name, __( 'اعتماد', QA_TEXT_DOMAIN ) );
            printf( '<button type="button" class="button qa-dismiss-ai" data-field="%s">%s</button>', $name, __( 'تجاهل', QA_TEXT_DOMAIN ) );
            echo '</div></div>';
        } else {
            // ai_textarea
            echo '<div class="qa-ai-textarea-field">';
            echo '<textarea id="' . $id . '" name="' . $name . '" rows="' . intval( $field['rows'] ?? 5 )
                . '" class="large-text qa-ai-field">' . esc_textarea( $value ) . '</textarea>';
            echo '<div class="qa-ai-field-actions">';
            printf( '<button type="button" class="button qa-accept-ai" data-field="%s">%s</button>', $name, __( 'اعتماد', QA_TEXT_DOMAIN ) );
            printf( '<button type="button" class="button qa-dismiss-ai" data-field="%s">%s</button>', $name, __( 'تجاهل', QA_TEXT_DOMAIN ) );
            echo '</div></div>';
        }
    }

    // ─── Post Select (searchable dropdown) ───────────────────────────────
    private function render_post_select( string $meta_key, array $field, int $post_id, $current_value ): void {
        $post_type = $field['post_type'] ?? 'post';
        $posts = get_posts( [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        echo '<select id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" class="qa-select qa-post-select">';
        echo '<option value="">' . __( '— بدون —', QA_TEXT_DOMAIN ) . '</option>';
        foreach ( $posts as $p ) {
            printf(
                '<option value="%d" %s>%s</option>',
                $p->ID,
                selected( intval( $current_value ), $p->ID, false ),
                esc_html( $p->post_title )
            );
        }
        echo '</select>';
    }

    // ─── Group fields by section ──────────────────────────────────────────
    private function group_by_section( array $schema, int $post_id ): array {
        $sections = [];
        foreach ( $schema as $meta_key => $field ) {
            $section = $field['section'] ?? 'core';
            $sections[ $section ][ $meta_key ] = $field;
        }
        return $sections;
    }

    // ─── Save ─────────────────────────────────────────────────────────────
    public function save( int $post_id, \WP_Post $post ): void {
        // Bail on autosave / AJAX
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
        if ( wp_is_post_revision( $post_id ) ) return;

        $nonce_map = [
            'qa_evidence' => [ 'nonce_name' => 'qa_evidence_nonce', 'action' => 'qa_evidence_save' ],
            'qa_event'    => [ 'nonce_name' => 'qa_event_nonce',    'action' => 'qa_event_save'    ],
            'qa_location' => [ 'nonce_name' => 'qa_location_nonce', 'action' => 'qa_location_save' ],
        ];

        if ( ! isset( $nonce_map[ $post->post_type ] ) ) return;

        $nonce_key    = $nonce_map[ $post->post_type ]['nonce_name'];
        $nonce_action = $nonce_map[ $post->post_type ]['action'];

        if ( empty( $_POST[ $nonce_key ] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ), $nonce_action ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $schema = MetaFields::schema()[ $post->post_type ] ?? [];

        foreach ( $schema as $meta_key => $field ) {
            if ( ! isset( $_POST[ $meta_key ] ) ) continue;

            // Lock AI fields from direct editing (only AI process updates them)
            if ( in_array( $field['input'], [ 'readonly' ] ) && strpos( $meta_key, 'qa_ai_' ) !== false ) {
                // Readonly AI system fields — skip
                // Exception: qa_ai_summary, qa_ai_tags etc. with accept buttons CAN be edited
                if ( in_array( $meta_key, [ 'qa_ai_status', 'qa_ai_last_run', 'qa_ai_error_message', 'qa_verification_by', 'qa_verification_date' ] ) ) {
                    continue;
                }
            }

            $raw   = wp_unslash( $_POST[ $meta_key ] );
            $clean = $this->sanitize_field( $raw, $field );

            $old = get_post_meta( $post_id, $meta_key, true );
            if ( $clean !== $old ) {
                update_post_meta( $post_id, $meta_key, $clean );
                // Log verification changes
                if ( $field['section'] === 'verification' ) {
                    $this->log_audit( $post_id, $meta_key, $old, $clean );
                }
            }
        }

        // Auto-stamp verification data when level changes to verified
        if ( isset( $_POST['qa_verification_level'] ) && $_POST['qa_verification_level'] === 'verified' ) {
            update_post_meta( $post_id, 'qa_verification_by', get_current_user_id() );
            update_post_meta( $post_id, 'qa_verification_date', current_time( 'mysql' ) );
        }
    }

    // ─── Sanitize ─────────────────────────────────────────────────────────
    private function sanitize_field( $value, array $field ): string {
        $input = $field['input'] ?? 'text';
        switch ( $input ) {
            case 'url':
                return esc_url_raw( $value );
            case 'date':
                return sanitize_text_field( $value );
            case 'textarea':
            case 'ai_textarea':
                return sanitize_textarea_field( $value );
            case 'json_editor':
            case 'ai_json':
                // Validate JSON
                $decoded = json_decode( $value );
                return ( json_last_error() === JSON_ERROR_NONE ) ? wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE ) : '';
            case 'select':
                $allowed = array_keys( $field['options'] ?? [] );
                return in_array( $value, $allowed, true ) ? $value : ( $field['default'] ?? '' );
            case 'post_select':
                return absint( $value ) ? (string) absint( $value ) : '';
            default:
                return sanitize_text_field( $value );
        }
    }

    // ─── Audit log ────────────────────────────────────────────────────────
    private function log_audit( int $post_id, string $field, $old, $new ): void {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'qa_audit_log',
            [
                'post_id'    => $post_id,
                'user_id'    => get_current_user_id(),
                'action'     => 'field_update',
                'field_name' => $field,
                'old_value'  => maybe_serialize( $old ),
                'new_value'  => maybe_serialize( $new ),
            ],
            [ '%d', '%d', '%s', '%s', '%s', '%s' ]
        );
    }
}
