<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin orchestrator — Singleton
 */
final class Plugin {

    private static ?Plugin $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function init(): void {
        $this->load_textdomain();
        $this->load_modules();
    }

    private function load_textdomain(): void {
        load_plugin_textdomain(
            QA_TEXT_DOMAIN,
            false,
            dirname( plugin_basename( QA_PLUGIN_FILE ) ) . '/languages'
        );
    }

    private function load_modules(): void {
        // Data layer
        ( new PostTypes() )->register();
        ( new Taxonomies() )->register();
        ( new MetaFields() )->register();

        // Performance
        ( new QueryOptimizer() )->register();

        // REST API
        ( new RestAPI() )->register();

        // AI + R2
        ( new R2\Manager() )->register();
        ( new AI\Manager() )->register();

        // Frontend templates + assets + shortcodes
        ( new Frontend\Templates() )->register();

        // Admin layer
        if ( is_admin() ) {
            ( new Admin\MetaBoxes() )->register();
            ( new Admin\Settings() )->register();
            ( new Admin\Columns() )->register();
            ( new Admin\Assets() )->register();
            ( new Admin\AIActions() )->register();
            $this->maybe_seed();
        }
    }

    /**
     * Allow seeding via URL param (admin only, one-time)
     */
    private function maybe_seed(): void {
        if ( ! isset( $_GET['qa_seed'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! wp_verify_nonce( $_GET['qa_seed_nonce'] ?? '', 'qa_seed' ) ) return;

        Seeder::run();
        wp_die( '✅ Seed data created! <a href="' . admin_url() . '">Back to Admin</a>' );
    }
}