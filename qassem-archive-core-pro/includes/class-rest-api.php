<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

/**
 * REST API — Phase 2
 *
 * Namespace : qassem/v1
 *
 * Public (no auth):
 *   GET /evidence          — filtered list + pagination
 *   GET /evidence/{id}     — single detail
 *   GET /map-points        — id,title,lat,lng,type,verification,event_date
 *   GET /timeline          — id,title,event_date,type,location  (ASC order)
 *   GET /locations         — full location list
 *   GET /events            — events list
 *   GET /filter-options    — all dropdown values in one request
 *
 * Auth (edit_posts):
 *   GET  /evidence/{id}/ai-status
 *   POST /evidence/{id}/run-ai
 *
 * All inputs: sanitized + validated via WP REST args schema.
 * Caching: transient 5 min, purged on save/delete/trash.
 */
class RestAPI {

    private const NS  = 'qassem/v1';
    private const TTL = 300;

    // ── Boot ─────────────────────────────────────────────────────────────

    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        foreach ( [ 'qa_evidence', 'qa_event', 'qa_location' ] as $cpt ) {
            add_action( "save_post_{$cpt}", [ $this, 'purge_cache' ] );
        }
        add_action( 'delete_post', [ $this, 'purge_cache' ] );
        add_action( 'trash_post',  [ $this, 'purge_cache' ] );
    }

    // ── Routes ───────────────────────────────────────────────────────────

    public function register_routes(): void {
        register_rest_route( self::NS, '/evidence', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_evidence_list' ],
            'permission_callback' => '__return_true',
            'args'                => $this->evidence_list_schema(),
        ] );
        register_rest_route( self::NS, '/evidence/(?P<id>\d+)', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_evidence_single' ],
            'permission_callback' => '__return_true',
            'args'                => [ 'id' => [ 'required' => true, 'sanitize_callback' => 'absint', 'validate_callback' => fn($v) => is_numeric($v) && $v > 0 ] ],
        ] );
        register_rest_route( self::NS, '/evidence/(?P<id>\d+)/ai-status', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_ai_status' ],
            'permission_callback' => [ $this, 'perm_edit' ],
            'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
        ] );
        register_rest_route( self::NS, '/evidence/(?P<id>\d+)/run-ai', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'run_ai' ],
            'permission_callback' => [ $this, 'perm_edit' ],
            'args'                => [ 'id' => [ 'sanitize_callback' => 'absint' ] ],
        ] );
        register_rest_route( self::NS, '/map-points', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_map_points' ],
            'permission_callback' => '__return_true',
            'args'                => $this->map_points_schema(),
        ] );
        register_rest_route( self::NS, '/timeline', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_timeline' ],
            'permission_callback' => '__return_true',
            'args'                => $this->timeline_schema(),
        ] );
        register_rest_route( self::NS, '/locations', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_locations' ],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( self::NS, '/events', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_events' ],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( self::NS, '/filter-options', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_filter_options' ],
            'permission_callback' => '__return_true',
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HANDLERS
    // ═══════════════════════════════════════════════════════════════════════

    public function get_evidence_list( \WP_REST_Request $req ): \WP_REST_Response {
        $ck     = 'qa_ev_' . md5( wp_json_encode( $req->get_params() ) );
        $cached = get_transient( $ck );
        if ( false !== $cached ) return new \WP_REST_Response( $cached );

        $per_page = min( absint( $req->get_param('per_page') ?: 20 ), 100 );
        $page     = max( 1, absint( $req->get_param('page') ?: 1 ) );

        $args = [
            'post_type'      => 'qa_evidence',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'meta_query'     => [ 'relation' => 'AND' ],
            'tax_query'      => [ 'relation' => 'AND' ],
            'no_found_rows'  => false,
        ];

        // Simple meta filters
        $this->add_meta( $args, $req, 'evidence_type',      'qa_evidence_type' );
        $this->add_meta( $args, $req, 'verification_level', 'qa_verification_level' );
        $this->add_meta( $args, $req, 'source_type',        'qa_source_type' );
        $this->add_meta( $args, $req, 'language',           'qa_language' );
        $this->add_meta( $args, $req, 'location_id',        'qa_location_id', '=', 'NUMERIC' );
        $this->add_meta( $args, $req, 'event_id',           'qa_event_id',    '=', 'NUMERIC' );

        // Date range
        $df = $this->clean_date( $req->get_param('date_from') );
        $dt = $this->clean_date( $req->get_param('date_to') );
        if ( $df && $dt ) {
            $args['meta_query'][] = [ 'key' => 'qa_event_date', 'value' => [ $df, $dt ], 'compare' => 'BETWEEN', 'type' => 'DATE' ];
        } elseif ( $df ) {
            $args['meta_query'][] = [ 'key' => 'qa_event_date', 'value' => $df, 'compare' => '>=', 'type' => 'DATE' ];
        } elseif ( $dt ) {
            $args['meta_query'][] = [ 'key' => 'qa_event_date', 'value' => $dt, 'compare' => '<=', 'type' => 'DATE' ];
        }

        // Year shortcut
        $year = absint( $req->get_param('year') ?: 0 );
        if ( $year >= 1900 && $year <= 2100 ) {
            $args['meta_query'][] = [ 'key' => 'qa_event_date', 'value' => [ "{$year}-01-01", "{$year}-12-31" ], 'compare' => 'BETWEEN', 'type' => 'DATE' ];
        }

        // Taxonomy
        $topic = sanitize_text_field( $req->get_param('topic') ?: '' );
        if ( $topic !== '' ) {
            $args['tax_query'][] = [ 'taxonomy' => 'qa_topic', 'field' => is_numeric($topic) ? 'term_id' : 'slug', 'terms' => is_numeric($topic) ? absint($topic) : $topic ];
        }
        $tag = sanitize_text_field( $req->get_param('tag') ?: '' );
        if ( $tag !== '' ) {
            $args['tax_query'][] = [ 'taxonomy' => 'qa_tag', 'field' => is_numeric($tag) ? 'term_id' : 'slug', 'terms' => is_numeric($tag) ? absint($tag) : $tag ];
        }

        // Search
        $s = sanitize_text_field( $req->get_param('search') ?: '' );
        if ( $s !== '' ) $args['s'] = $s;

        // Order
        $ob    = sanitize_key( $req->get_param('orderby') ?: 'event_date' );
        $order = strtoupper( sanitize_key( $req->get_param('order') ?: 'DESC' ) );
        $order = in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'DESC';
        if ( $ob === 'title' )      { $args['orderby'] = 'title'; }
        elseif ( $ob === 'date' )   { $args['orderby'] = 'date'; }
        else                        { $args['meta_key'] = 'qa_event_date'; $args['orderby'] = 'meta_value'; }
        $args['order'] = $order;

        // Clean empty clauses
        if ( count( $args['meta_query'] ) <= 1 ) unset( $args['meta_query'] );
        if ( count( $args['tax_query'] )  <= 1 ) unset( $args['tax_query'] );

        $q    = new \WP_Query( $args );
        $body = [
            'items'       => array_map( [ $this, 'card' ], $q->posts ),
            'total'       => (int) $q->found_posts,
            'total_pages' => (int) $q->max_num_pages,
            'page'        => $page,
            'per_page'    => $per_page,
        ];

        set_transient( $ck, $body, self::TTL );
        $res = new \WP_REST_Response( $body );
        $res->header( 'X-QA-Total',      $q->found_posts );
        $res->header( 'X-QA-TotalPages', $q->max_num_pages );
        return $res;
    }

    public function get_evidence_single( \WP_REST_Request $req ): \WP_REST_Response {
        $id   = absint( $req->get_param('id') );
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'qa_evidence' || $post->post_status !== 'publish' ) {
            return new \WP_REST_Response( [ 'message' => 'الدليل غير موجود' ], 404 );
        }
        return new \WP_REST_Response( $this->full( $post ) );
    }

    public function get_map_points( \WP_REST_Request $req ): \WP_REST_Response {
        $ck     = 'qa_map_' . md5( wp_json_encode( $req->get_params() ) );
        $cached = get_transient( $ck );
        if ( false !== $cached ) return new \WP_REST_Response( $cached );

        $mq = [ 'relation' => 'AND', [ 'key' => 'qa_location_id', 'value' => '', 'compare' => '!=' ] ];

        foreach ( [ 'evidence_type' => 'qa_evidence_type', 'verification_level' => 'qa_verification_level' ] as $p => $k ) {
            $v = sanitize_text_field( $req->get_param($p) ?: '' );
            if ( $v !== '' ) $mq[] = [ 'key' => $k, 'value' => $v ];
        }
        foreach ( [ 'event_id' => 'qa_event_id', 'location_id' => 'qa_location_id' ] as $p => $k ) {
            $v = absint( $req->get_param($p) ?: 0 );
            if ( $v ) $mq[] = [ 'key' => $k, 'value' => $v, 'type' => 'NUMERIC' ];
        }

        $ids    = ( new \WP_Query( [ 'post_type' => 'qa_evidence', 'post_status' => 'publish', 'posts_per_page' => 2000, 'fields' => 'ids', 'meta_query' => $mq, 'no_found_rows' => true ] ) )->posts;
        $points = [];

        foreach ( $ids as $id ) {
            $loc = (int) get_post_meta( $id, 'qa_location_id', true );
            if ( ! $loc ) continue;
            $lat = (float) get_post_meta( $loc, 'qa_lat', true );
            $lng = (float) get_post_meta( $loc, 'qa_lng', true );
            if ( ! $lat || ! $lng ) continue;
            $points[] = [
                'id'                 => $id,
                'title'              => get_the_title( $id ),
                'lat'                => $lat,
                'lng'                => $lng,
                'evidence_type'      => get_post_meta( $id, 'qa_evidence_type',      true ),
                'verification_level' => get_post_meta( $id, 'qa_verification_level', true ) ?: 'unverified',
                'event_date'         => get_post_meta( $id, 'qa_event_date',         true ),
                'location_id'        => $loc,
                'location_name'      => get_the_title( $loc ),
                'thumb_url'          => get_post_meta( $id, 'qa_thumb_url', true ) ?: '',
                'permalink'          => get_permalink( $id ),
            ];
        }

        $body = [ 'points' => $points, 'total' => count( $points ) ];
        set_transient( $ck, $body, self::TTL );
        return new \WP_REST_Response( $body );
    }

    public function get_timeline( \WP_REST_Request $req ): \WP_REST_Response {
        $ck     = 'qa_tl_' . md5( wp_json_encode( $req->get_params() ) );
        $cached = get_transient( $ck );
        if ( false !== $cached ) return new \WP_REST_Response( $cached );

        $yf = max( 1900, min( absint( $req->get_param('year_from') ?: 2000 ), 2100 ) );
        $yt = max( 1900, min( absint( $req->get_param('year_to')   ?: (int) date('Y') ), 2100 ) );

        $mq = [ 'relation' => 'AND', [ 'key' => 'qa_event_date', 'value' => [ "{$yf}-01-01", "{$yt}-12-31" ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ];

        foreach ( [ 'evidence_type' => 'qa_evidence_type', 'verification_level' => 'qa_verification_level' ] as $p => $k ) {
            $v = sanitize_text_field( $req->get_param($p) ?: '' );
            if ( $v !== '' ) $mq[] = [ 'key' => $k, 'value' => $v ];
        }
        foreach ( [ 'location_id' => 'qa_location_id', 'event_id' => 'qa_event_id' ] as $p => $k ) {
            $v = absint( $req->get_param($p) ?: 0 );
            if ( $v ) $mq[] = [ 'key' => $k, 'value' => $v, 'type' => 'NUMERIC' ];
        }

        $ids    = ( new \WP_Query( [ 'post_type' => 'qa_evidence', 'post_status' => 'publish', 'posts_per_page' => 2000, 'fields' => 'ids', 'meta_query' => $mq, 'meta_key' => 'qa_event_date', 'orderby' => 'meta_value', 'order' => 'ASC', 'no_found_rows' => true ] ) )->posts;
        $items  = [];
        $grouped = [];

        foreach ( $ids as $id ) {
            $loc  = (int) get_post_meta( $id, 'qa_location_id', true );
            $date = get_post_meta( $id, 'qa_event_date', true );
            $item = [
                'id'                 => $id,
                'title'              => get_the_title( $id ),
                'event_date'         => $date,
                'evidence_type'      => get_post_meta( $id, 'qa_evidence_type',      true ),
                'verification_level' => get_post_meta( $id, 'qa_verification_level', true ) ?: 'unverified',
                'location_id'        => $loc,
                'location_name'      => $loc ? get_the_title( $loc ) : '',
                'event_id'           => (int) get_post_meta( $id, 'qa_event_id', true ),
                'thumb_url'          => get_post_meta( $id, 'qa_thumb_url', true ) ?: '',
                'permalink'          => get_permalink( $id ),
            ];
            $items[] = $item;
            $ym = substr( $date ?: '', 0, 7 );
            if ( $ym ) $grouped[$ym][] = $item;
        }
        ksort( $grouped );

        $body = [ 'items' => $items, 'grouped' => $grouped, 'total' => count($items) ];
        set_transient( $ck, $body, self::TTL );
        return new \WP_REST_Response( $body );
    }

    public function get_locations( \WP_REST_Request $req ): \WP_REST_Response {
        $cached = get_transient('qa_locs_v2');
        if ( false !== $cached ) return new \WP_REST_Response( $cached );
        $posts = get_posts( [ 'post_type' => 'qa_location', 'post_status' => 'publish', 'posts_per_page' => 500, 'orderby' => 'title', 'order' => 'ASC' ] );
        $locs  = array_map( fn($p) => [
            'id'        => $p->ID,
            'title'     => $p->post_title,
            'lat'       => (float) get_post_meta( $p->ID, 'qa_lat', true ),
            'lng'       => (float) get_post_meta( $p->ID, 'qa_lng', true ),
            'precision' => get_post_meta( $p->ID, 'qa_geo_precision', true ),
            'parent_id' => (int) get_post_meta( $p->ID, 'qa_parent_location_id', true ),
            'aliases'   => get_post_meta( $p->ID, 'qa_aliases', true ),
            'permalink' => get_permalink( $p->ID ),
        ], $posts );
        $body = [ 'locations' => $locs, 'total' => count($locs) ];
        set_transient( 'qa_locs_v2', $body, self::TTL );
        return new \WP_REST_Response( $body );
    }

    public function get_events( \WP_REST_Request $req ): \WP_REST_Response {
        $cached = get_transient('qa_evts_v2');
        if ( false !== $cached ) return new \WP_REST_Response( $cached );
        $posts  = get_posts( [ 'post_type' => 'qa_event', 'post_status' => 'publish', 'posts_per_page' => 200, 'meta_key' => 'qa_start_date', 'orderby' => 'meta_value', 'order' => 'DESC' ] );
        $events = array_map( fn($p) => [
            'id'          => $p->ID,
            'title'       => $p->post_title,
            'start_date'  => get_post_meta( $p->ID, 'qa_start_date',    true ),
            'end_date'    => get_post_meta( $p->ID, 'qa_end_date',      true ),
            'location_id' => (int) get_post_meta( $p->ID, 'qa_location_id', true ),
            'summary'     => get_post_meta( $p->ID, 'qa_event_summary', true ),
            'permalink'   => get_permalink( $p->ID ),
        ], $posts );
        $body = [ 'events' => $events, 'total' => count($events) ];
        set_transient( 'qa_evts_v2', $body, self::TTL );
        return new \WP_REST_Response( $body );
    }

    public function get_filter_options( \WP_REST_Request $req ): \WP_REST_Response {
        $cached = get_transient('qa_fopts_v2');
        if ( false !== $cached ) return new \WP_REST_Response( $cached );

        global $wpdb;
        $years_raw = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT YEAR(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key=%s AND p.post_type=%s AND p.post_status='publish' AND pm.meta_value!='' ORDER BY YEAR(pm.meta_value) DESC",
            'qa_event_date', 'qa_evidence'
        ) );
        $years = array_values( array_filter( array_map( 'absint', $years_raw ) ) );

        $locs_raw   = get_posts( [ 'post_type' => 'qa_location', 'post_status' => 'publish', 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC' ] );
        $evts_raw   = get_posts( [ 'post_type' => 'qa_event',    'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ] );
        $topics_raw = get_terms( [ 'taxonomy' => 'qa_topic', 'hide_empty' => true, 'number' => 200 ] );
        $tags_raw   = get_terms( [ 'taxonomy' => 'qa_tag',   'hide_empty' => true, 'number' => 50  ] );

        $body = [
            'evidence_types' => [
                [ 'value' => 'video',     'label' => 'فيديو',  'icon' => '🎬', 'color' => '#e74c3c' ],
                [ 'value' => 'photo',     'label' => 'صورة',   'icon' => '📷', 'color' => '#9b59b6' ],
                [ 'value' => 'document',  'label' => 'وثيقة',  'icon' => '📄', 'color' => '#3498db' ],
                [ 'value' => 'testimony', 'label' => 'شهادة',  'icon' => '💬', 'color' => '#27ae60' ],
            ],
            'verification_levels' => [
                [ 'value' => 'verified',   'label' => 'محقق',     'color' => '#2ecc71' ],
                [ 'value' => 'probable',   'label' => 'غالب',     'color' => '#3498db' ],
                [ 'value' => 'possible',   'label' => 'محتمل',    'color' => '#f39c12' ],
                [ 'value' => 'unverified', 'label' => 'غير محقق', 'color' => '#636e72' ],
            ],
            'source_types' => [
                [ 'value' => 'eyewitness',   'label' => 'شاهد عيان' ],
                [ 'value' => 'activist',     'label' => 'ناشط' ],
                [ 'value' => 'media',        'label' => 'وسائل إعلام' ],
                [ 'value' => 'organization', 'label' => 'منظمة' ],
                [ 'value' => 'archive',      'label' => 'أرشيف' ],
            ],
            'years'     => $years,
            'locations' => array_map( fn($p) => [ 'id' => $p->ID, 'title' => $p->post_title ], $locs_raw ),
            'events'    => array_map( fn($p) => [ 'id' => $p->ID, 'title' => $p->post_title, 'start_date' => get_post_meta($p->ID,'qa_start_date',true) ], $evts_raw ),
            'topics'    => is_wp_error($topics_raw) ? [] : array_map( fn($t) => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'parent' => $t->parent, 'count' => $t->count ], $topics_raw ),
            'tags'      => is_wp_error($tags_raw)   ? [] : array_map( fn($t) => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'count' => $t->count ], $tags_raw ),
        ];

        set_transient( 'qa_fopts_v2', $body, self::TTL );
        return new \WP_REST_Response( $body );
    }

    public function get_ai_status( \WP_REST_Request $req ): \WP_REST_Response {
        $id = absint( $req->get_param('id') );
        return new \WP_REST_Response( [
            'post_id'       => $id,
            'ai_status'     => get_post_meta( $id, 'qa_ai_status',        true ) ?: 'idle',
            'ai_last_run'   => get_post_meta( $id, 'qa_ai_last_run',      true ),
            'error_message' => get_post_meta( $id, 'qa_ai_error_message', true ),
        ] );
    }

    public function run_ai( \WP_REST_Request $req ): \WP_REST_Response {
        $id   = absint( $req->get_param('id') );
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'qa_evidence' ) {
            return new \WP_REST_Response( [ 'message' => 'الدليل غير موجود' ], 404 );
        }
        if ( get_post_meta( $id, 'qa_ai_status', true ) === 'processing' ) {
            return new \WP_REST_Response( [ 'message' => 'المعالجة جارية بالفعل' ], 409 );
        }
        update_post_meta( $id, 'qa_ai_status',   'processing' );
        update_post_meta( $id, 'qa_ai_last_run', current_time('mysql') );
        do_action( 'qa_run_ai_analysis', $id ); // Phase 3 hook
        return new \WP_REST_Response( [ 'message' => 'تمت جدولة التحليل', 'post_id' => $id ], 202 );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // FORMATTERS
    // ═══════════════════════════════════════════════════════════════════════

    private function card( \WP_Post $p ): array {
        $id  = $p->ID;
        $loc = (int) get_post_meta( $id, 'qa_location_id', true );
        $evt = (int) get_post_meta( $id, 'qa_event_id',    true );
        $d   = get_post_meta( $id, 'qa_event_date', true );
        return [
            'id'                 => $id,
            'title'              => $p->post_title,
            'excerpt'            => wp_trim_words( $p->post_content, 25, '…' ),
            'evidence_type'      => get_post_meta( $id, 'qa_evidence_type',      true ),
            'event_date'         => $d,
            'event_date_display' => $this->date_ar( $d ),
            'location_id'        => $loc,
            'location_name'      => $loc ? get_the_title( $loc ) : '',
            'event_id'           => $evt,
            'event_name'         => $evt ? get_the_title( $evt ) : '',
            'source_type'        => get_post_meta( $id, 'qa_source_type',        true ),
            'verification_level' => get_post_meta( $id, 'qa_verification_level', true ) ?: 'unverified',
            'media_url'          => get_post_meta( $id, 'qa_media_url',          true ),
            'thumb_url'          => get_post_meta( $id, 'qa_thumb_url',          true ) ?: '',
            'ai_status'          => get_post_meta( $id, 'qa_ai_status',          true ) ?: 'idle',
            'ai_summary'         => get_post_meta( $id, 'qa_ai_summary',         true ),
            'topics'             => wp_get_post_terms( $id, 'qa_topic', [ 'fields' => 'names' ] ) ?: [],
            'tags'               => wp_get_post_terms( $id, 'qa_tag',   [ 'fields' => 'names' ] ) ?: [],
            'permalink'          => get_permalink( $id ),
        ];
    }

    private function full( \WP_Post $p ): array {
        $id   = $p->ID;
        $data = $this->card( $p );
        $data['content']           = wpautop( $p->post_content );
        $data['verification_notes']= get_post_meta( $id, 'qa_verification_notes', true );
        $data['verification_by']   = (int) get_post_meta( $id, 'qa_verification_by',   true );
        $data['verification_date'] = get_post_meta( $id, 'qa_verification_date',  true );
        $data['source_url']        = get_post_meta( $id, 'qa_source_url',         true );
        $data['language']          = get_post_meta( $id, 'qa_language',           true );
        $data['media_storage']     = get_post_meta( $id, 'qa_media_storage',      true );
        $data['file_size']         = get_post_meta( $id, 'qa_file_size',          true );
        $data['file_duration']     = get_post_meta( $id, 'qa_file_duration',      true );
        $data['ai_tags']           = json_decode( get_post_meta($id,'qa_ai_tags',     true) ?: '[]', true );
        $data['ai_entities']       = json_decode( get_post_meta($id,'qa_ai_entities', true) ?: '{}', true );
        $data['ai_transcript']     = get_post_meta( $id, 'qa_ai_transcript', true );
        $data['ai_ocr_text']       = get_post_meta( $id, 'qa_ai_ocr_text',   true );

        $rm = [ 'relation' => 'OR' ];
        if ( $data['event_id'] )    $rm[] = [ 'key' => 'qa_event_id',    'value' => $data['event_id'],    'type' => 'NUMERIC' ];
        if ( $data['location_id'] ) $rm[] = [ 'key' => 'qa_location_id', 'value' => $data['location_id'], 'type' => 'NUMERIC' ];
        $data['related'] = count($rm) > 1
            ? array_map( [ $this, 'card' ], ( new \WP_Query( [ 'post_type' => 'qa_evidence', 'post_status' => 'publish', 'posts_per_page' => 4, 'post__not_in' => [$id], 'meta_query' => $rm, 'no_found_rows' => true ] ) )->posts )
            : [];
        return $data;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function add_meta( array &$args, \WP_REST_Request $req, string $param, string $key, string $compare = '=', string $type = 'CHAR' ): void {
        $v = $req->get_param($param);
        if ( $v === null || $v === '' ) return;
        $v = $type === 'NUMERIC' ? absint($v) : sanitize_text_field($v);
        if ( $type === 'NUMERIC' && ! $v ) return;
        if ( $type !== 'NUMERIC' && $v === '' ) return;
        $c = [ 'key' => $key, 'value' => $v, 'compare' => $compare ];
        if ( $type !== 'CHAR' ) $c['type'] = $type;
        $args['meta_query'][] = $c;
    }

    private function clean_date( ?string $d ): string {
        if ( ! $d ) return '';
        $d = sanitize_text_field( $d );
        return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d ) ? $d : '';
    }

    private function date_ar( string $d ): string {
        if ( ! $d ) return '';
        static $m = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $ts = strtotime($d);
        return $ts ? (int)date('d',$ts).' '.$m[(int)date('m',$ts)].' '.date('Y',$ts) : $d;
    }

    public function perm_edit(): bool { return current_user_can('edit_posts'); }

    public function purge_cache(): void {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_qa_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_qa_%'");
    }

    // ─── Arg schemas ─────────────────────────────────────────────────────

    private function evidence_list_schema(): array {
        return [
            'page'               => [ 'type' => 'integer', 'default' => 1,   'minimum' => 1,    'sanitize_callback' => 'absint' ],
            'per_page'           => [ 'type' => 'integer', 'default' => 20,  'minimum' => 1,   'maximum' => 100, 'sanitize_callback' => 'absint' ],
            'evidence_type'      => [ 'type' => 'string',  'enum' => [ 'video', 'photo', 'document', 'testimony' ] ],
            'verification_level' => [ 'type' => 'string',  'enum' => [ 'verified', 'probable', 'possible', 'unverified' ] ],
            'source_type'        => [ 'type' => 'string',  'enum' => [ 'eyewitness', 'activist', 'media', 'organization', 'archive' ] ],
            'language'           => [ 'type' => 'string',  'enum' => [ 'ar', 'en', 'fr' ] ],
            'location_id'        => [ 'type' => 'integer', 'minimum' => 1, 'sanitize_callback' => 'absint' ],
            'event_id'           => [ 'type' => 'integer', 'minimum' => 1, 'sanitize_callback' => 'absint' ],
            'date_from'          => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'validate_callback' => fn($v) => !$v || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/',$v) ],
            'date_to'            => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field', 'validate_callback' => fn($v) => !$v || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/',$v) ],
            'year'               => [ 'type' => 'integer', 'minimum' => 1900, 'maximum' => 2100, 'sanitize_callback' => 'absint' ],
            'topic'              => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'tag'                => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'search'             => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'orderby'            => [ 'type' => 'string',  'default' => 'event_date', 'enum' => [ 'event_date', 'title', 'date' ] ],
            'order'              => [ 'type' => 'string',  'default' => 'DESC',       'enum' => [ 'ASC', 'DESC' ] ],
        ];
    }

    private function map_points_schema(): array {
        return [
            'evidence_type'      => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'verification_level' => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'event_id'           => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
            'location_id'        => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
        ];
    }

    private function timeline_schema(): array {
        return [
            'year_from'          => [ 'type' => 'integer', 'default' => 2000, 'minimum' => 1900, 'maximum' => 2100, 'sanitize_callback' => 'absint' ],
            'year_to'            => [ 'type' => 'integer', 'default' => (int)date('Y'), 'minimum' => 1900, 'maximum' => 2100, 'sanitize_callback' => 'absint' ],
            'evidence_type'      => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'verification_level' => [ 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
            'location_id'        => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
            'event_id'           => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
        ];
    }
}
