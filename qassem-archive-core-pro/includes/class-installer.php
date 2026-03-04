<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

class Installer {

    public static function activate(): void {
        // Register CPTs first so flush works
        ( new PostTypes() )->register();
        ( new Taxonomies() )->register();

        // Create custom DB tables if needed
        self::create_tables();
        QueryOptimizer::create_table();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Store version
        update_option( 'qa_version', QA_VERSION );
        update_option( 'qa_activated_at', current_time( 'mysql' ) );

        // Default options
        if ( ! get_option( 'qa_settings' ) ) {
            update_option( 'qa_settings', self::default_settings() );
        }
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    private static function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Audit log table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qa_audit_log (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id     BIGINT(20) UNSIGNED NOT NULL,
            user_id     BIGINT(20) UNSIGNED NOT NULL,
            action      VARCHAR(100) NOT NULL,
            field_name  VARCHAR(100) DEFAULT NULL,
            old_value   LONGTEXT DEFAULT NULL,
            new_value   LONGTEXT DEFAULT NULL,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // AI audit log table (Phase 4)
        $sql_ai = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}qa_ai_audit (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            evidence_id BIGINT(20) UNSIGNED NOT NULL,
            user_id     BIGINT(20) UNSIGNED NULL,
            action      VARCHAR(40) NOT NULL,
            data_before LONGTEXT DEFAULT NULL,
            data_after  LONGTEXT DEFAULT NULL,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY evidence_id (evidence_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        dbDelta( $sql_ai );

    }

    private static function default_settings(): array {
        return [
            // R2
            'r2_account_id'    => '',
            'r2_access_key'    => '',
            'r2_secret_key'    => '',
            'r2_bucket_name'   => '',
            'r2_public_url'    => '',
            // AI
            'ai_provider'      => 'openai',
            'ai_api_key'       => '',
            'ai_model'         => 'gpt-4o',
            'ai_language'      => 'ar',
            // General
            'default_map_lat'  => '35.1667',
            'default_map_lng'  => '35.9333',
            'default_map_zoom' => '10',
        ];
    }
}
