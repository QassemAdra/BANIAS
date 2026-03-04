<?php
namespace QA\Admin;

defined( 'ABSPATH' ) || exit;

class Columns {

    public function register(): void {
        // Evidence columns
        add_filter( 'manage_qa_evidence_posts_columns',       [ $this, 'evidence_columns' ] );
        add_action( 'manage_qa_evidence_posts_custom_column', [ $this, 'evidence_column_data' ], 10, 2 );
        add_filter( 'manage_edit-qa_evidence_sortable_columns', [ $this, 'evidence_sortable' ] );

        // Event columns
        add_filter( 'manage_qa_event_posts_columns',       [ $this, 'event_columns' ] );
        add_action( 'manage_qa_event_posts_custom_column', [ $this, 'event_column_data' ], 10, 2 );

        // Location columns
        add_filter( 'manage_qa_location_posts_columns',       [ $this, 'location_columns' ] );
        add_action( 'manage_qa_location_posts_custom_column', [ $this, 'location_column_data' ], 10, 2 );

        // Sortable queries
        add_action( 'pre_get_posts', [ $this, 'handle_sortable' ] );
    }

    // ─── Evidence ─────────────────────────────────────────────────────────
    public function evidence_columns( array $columns ): array {
        $new = [];
        $new['cb']                   = $columns['cb'];
        $new['qa_thumb']             = '';
        $new['title']                = __( 'العنوان', QA_TEXT_DOMAIN );
        $new['qa_evidence_type']     = __( 'النوع', QA_TEXT_DOMAIN );
        $new['qa_event_date']        = __( 'تاريخ الحدث', QA_TEXT_DOMAIN );
        $new['qa_location']          = __( 'الموقع', QA_TEXT_DOMAIN );
        $new['qa_verification_level'] = __( 'التحقق', QA_TEXT_DOMAIN );
        $new['qa_ai_status']         = __( 'AI', QA_TEXT_DOMAIN );
        $new['date']                 = __( 'تاريخ النشر', QA_TEXT_DOMAIN );
        return $new;
    }

    public function evidence_column_data( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'qa_thumb':
                $thumb = get_post_meta( $post_id, 'qa_thumb_url', true );
                if ( $thumb ) {
                    echo '<img src="' . esc_url( $thumb ) . '" width="50" height="50" style="object-fit:cover;border-radius:4px;">';
                } elseif ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, [ 50, 50 ] );
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:30px;"></span>';
                }
                break;

            case 'qa_evidence_type':
                $type = get_post_meta( $post_id, 'qa_evidence_type', true );
                $icons = [ 'video' => '🎬', 'photo' => '📷', 'document' => '📄', 'testimony' => '💬' ];
                $labels = [
                    'video'     => __( 'فيديو', QA_TEXT_DOMAIN ),
                    'photo'     => __( 'صورة', QA_TEXT_DOMAIN ),
                    'document'  => __( 'وثيقة', QA_TEXT_DOMAIN ),
                    'testimony' => __( 'شهادة', QA_TEXT_DOMAIN ),
                ];
                echo '<span class="qa-type-badge qa-type-' . esc_attr( $type ) . '">';
                echo ( $icons[ $type ] ?? '📎' ) . ' ' . esc_html( $labels[ $type ] ?? $type );
                echo '</span>';
                break;

            case 'qa_event_date':
                $date = get_post_meta( $post_id, 'qa_event_date', true );
                echo $date ? esc_html( $date ) : '—';
                break;

            case 'qa_location':
                $loc_id = get_post_meta( $post_id, 'qa_location_id', true );
                if ( $loc_id ) {
                    $loc = get_post( $loc_id );
                    if ( $loc ) echo esc_html( $loc->post_title );
                } else {
                    echo '—';
                }
                break;

            case 'qa_verification_level':
                $level = get_post_meta( $post_id, 'qa_verification_level', true ) ?: 'unverified';
                $badges = [
                    'unverified' => [ 'label' => __( 'غير محقق', QA_TEXT_DOMAIN ), 'class' => 'qa-badge-grey' ],
                    'possible'   => [ 'label' => __( 'محتمل', QA_TEXT_DOMAIN ),    'class' => 'qa-badge-yellow' ],
                    'probable'   => [ 'label' => __( 'غالب', QA_TEXT_DOMAIN ),     'class' => 'qa-badge-blue' ],
                    'verified'   => [ 'label' => __( 'محقق', QA_TEXT_DOMAIN ),     'class' => 'qa-badge-green' ],
                ];
                $b = $badges[ $level ] ?? $badges['unverified'];
                echo '<span class="qa-badge ' . esc_attr( $b['class'] ) . '">' . esc_html( $b['label'] ) . '</span>';
                break;

            case 'qa_ai_status':
                $status = get_post_meta( $post_id, 'qa_ai_status', true ) ?: 'idle';
                $icons  = [ 'idle' => '⏳', 'processing' => '🔄', 'ready' => '✅', 'error' => '❌' ];
                echo $icons[ $status ] ?? '—';
                break;
        }
    }

    public function evidence_sortable( array $columns ): array {
        $columns['qa_event_date']         = 'qa_event_date';
        $columns['qa_verification_level'] = 'qa_verification_level';
        return $columns;
    }

    // ─── Event ────────────────────────────────────────────────────────────
    public function event_columns( array $columns ): array {
        return [
            'cb'             => $columns['cb'],
            'title'          => __( 'العنوان', QA_TEXT_DOMAIN ),
            'qa_start_date'  => __( 'تاريخ البداية', QA_TEXT_DOMAIN ),
            'qa_end_date'    => __( 'تاريخ النهاية', QA_TEXT_DOMAIN ),
            'qa_location'    => __( 'الموقع', QA_TEXT_DOMAIN ),
            'qa_evidence_count' => __( 'عدد الأدلة', QA_TEXT_DOMAIN ),
            'date'           => __( 'تاريخ النشر', QA_TEXT_DOMAIN ),
        ];
    }

    public function event_column_data( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'qa_start_date':
                echo esc_html( get_post_meta( $post_id, 'qa_start_date', true ) ?: '—' );
                break;
            case 'qa_end_date':
                echo esc_html( get_post_meta( $post_id, 'qa_end_date', true ) ?: '—' );
                break;
            case 'qa_location':
                $loc_id = get_post_meta( $post_id, 'qa_location_id', true );
                echo $loc_id ? esc_html( get_the_title( $loc_id ) ) : '—';
                break;
            case 'qa_evidence_count':
                $count = new \WP_Query( [
                    'post_type'      => 'qa_evidence',
                    'meta_key'       => 'qa_event_id',
                    'meta_value'     => $post_id,
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ] );
                echo '<span class="qa-count-badge">' . intval( $count->found_posts ) . '</span>';
                break;
        }
    }

    // ─── Location ─────────────────────────────────────────────────────────
    public function location_columns( array $columns ): array {
        return [
            'cb'               => $columns['cb'],
            'title'            => __( 'الاسم', QA_TEXT_DOMAIN ),
            'qa_geo_precision' => __( 'الدقة الجغرافية', QA_TEXT_DOMAIN ),
            'qa_lat_lng'       => __( 'الإحداثيات', QA_TEXT_DOMAIN ),
            'qa_evidence_count' => __( 'الأدلة', QA_TEXT_DOMAIN ),
            'date'             => __( 'تاريخ الإضافة', QA_TEXT_DOMAIN ),
        ];
    }

    public function location_column_data( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'qa_geo_precision':
                $precision = get_post_meta( $post_id, 'qa_geo_precision', true );
                $labels = [ 'city' => __( 'مدينة', QA_TEXT_DOMAIN ), 'district' => __( 'منطقة', QA_TEXT_DOMAIN ),
                            'neighborhood' => __( 'حي', QA_TEXT_DOMAIN ), 'exact' => __( 'دقيق', QA_TEXT_DOMAIN ), 'unknown' => __( 'غير معروف', QA_TEXT_DOMAIN ) ];
                echo esc_html( $labels[ $precision ] ?? $precision ?: '—' );
                break;
            case 'qa_lat_lng':
                $lat = get_post_meta( $post_id, 'qa_lat', true );
                $lng = get_post_meta( $post_id, 'qa_lng', true );
                echo ( $lat && $lng ) ? esc_html( "{$lat}, {$lng}" ) : '—';
                break;
            case 'qa_evidence_count':
                $count = new \WP_Query( [
                    'post_type'      => 'qa_evidence',
                    'meta_key'       => 'qa_location_id',
                    'meta_value'     => $post_id,
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ] );
                echo '<span class="qa-count-badge">' . intval( $count->found_posts ) . '</span>';
                break;
        }
    }

    // ─── Sortable handler ─────────────────────────────────────────────────
    public function handle_sortable( \WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) return;
        $orderby = $query->get( 'orderby' );
        if ( $orderby === 'qa_event_date' ) {
            $query->set( 'meta_key', 'qa_event_date' );
            $query->set( 'orderby',  'meta_value' );
        }
    }
}
