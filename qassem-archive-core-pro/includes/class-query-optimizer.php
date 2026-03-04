<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

/**
 * Performance helpers:
 * - Custom meta index table for fast evidence queries
 * - Query optimisations for filter combinations
 */
class QueryOptimizer {

    public function register(): void {
        add_action( 'init',              [ $this, 'maybe_create_index_table' ] );
        add_action( 'save_post_qa_evidence', [ $this, 'update_evidence_index' ], 20, 1 );
        add_action( 'delete_post',           [ $this, 'delete_evidence_index' ], 10, 1 );
    }

    // ─── Create index table on plugin activation ──────────────────────────
    public static function create_table(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qa_evidence_index (
            post_id             BIGINT(20) UNSIGNED NOT NULL,
            evidence_type       VARCHAR(20)  DEFAULT '',
            event_date          DATE         DEFAULT NULL,
            event_date_year     SMALLINT     DEFAULT NULL,
            location_id         BIGINT(20)   DEFAULT 0,
            event_id            BIGINT(20)   DEFAULT 0,
            source_type         VARCHAR(20)  DEFAULT '',
            verification_level  VARCHAR(20)  DEFAULT 'unverified',
            ai_status           VARCHAR(20)  DEFAULT 'idle',
            has_media           TINYINT(1)   DEFAULT 0,
            PRIMARY KEY (post_id),
            KEY idx_evidence_type (evidence_type),
            KEY idx_event_date    (event_date),
            KEY idx_year          (event_date_year),
            KEY idx_location      (location_id),
            KEY idx_event         (event_id),
            KEY idx_verification  (verification_level),
            KEY idx_compound      (event_date, verification_level, evidence_type)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    // ─── Update index when evidence is saved ──────────────────────────────
    public function update_evidence_index( int $post_id ): void {
        if ( get_post_status( $post_id ) !== 'publish' ) {
            $this->delete_evidence_index( $post_id );
            return;
        }

        global $wpdb;
        $date = get_post_meta( $post_id, 'qa_event_date', true );

        $wpdb->replace(
            $wpdb->prefix . 'qa_evidence_index',
            [
                'post_id'            => $post_id,
                'evidence_type'      => get_post_meta( $post_id, 'qa_evidence_type', true ),
                'event_date'         => $date ?: null,
                'event_date_year'    => $date ? (int) date( 'Y', strtotime( $date ) ) : null,
                'location_id'        => (int) get_post_meta( $post_id, 'qa_location_id', true ),
                'event_id'           => (int) get_post_meta( $post_id, 'qa_event_id', true ),
                'source_type'        => get_post_meta( $post_id, 'qa_source_type', true ),
                'verification_level' => get_post_meta( $post_id, 'qa_verification_level', true ) ?: 'unverified',
                'ai_status'          => get_post_meta( $post_id, 'qa_ai_status', true ) ?: 'idle',
                'has_media'          => get_post_meta( $post_id, 'qa_media_url', true ) ? 1 : 0,
            ],
            [ '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d' ]
        );
    }

    public function delete_evidence_index( int $post_id ): void {
        global $wpdb;
        $post = get_post( $post_id );
        if ( $post && $post->post_type === 'qa_evidence' ) {
            $wpdb->delete( $wpdb->prefix . 'qa_evidence_index', [ 'post_id' => $post_id ], [ '%d' ] );
        }
    }

    public function maybe_create_index_table(): void {
        if ( get_option( 'qa_index_version' ) !== QA_VERSION ) {
            self::create_table();
            update_option( 'qa_index_version', QA_VERSION );
        }
    }

    // ─── Rebuild full index (admin tool) ──────────────────────────────────
    public static function rebuild_index(): int {
        $posts = get_posts( [
            'post_type'      => 'qa_evidence',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        $optimizer = new self();
        foreach ( $posts as $id ) {
            $optimizer->update_evidence_index( $id );
        }
        return count( $posts );
    }
}
