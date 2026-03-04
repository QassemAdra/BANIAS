<?php
/**
 * Plugin Name:       Qassem Archive Core
 * Plugin URI:        https://qassem-archive.org
 * Description:       منصة أرشيف الأدلة التاريخية — بانياس والساحل السوري
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Qassem Archive Team
 * Text Domain:       qassem-archive
 * Domain Path:       /languages
 * License:           GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

// ─── Constants ───────────────────────────────────────────────────────────────
define( 'QA_VERSION',     '1.0.0' );
define( 'QA_PLUGIN_FILE', __FILE__ );
define( 'QA_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'QA_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'QA_TEXT_DOMAIN', 'qassem-archive' );

// ─── Autoload ────────────────────────────────────────────────────────────────
spl_autoload_register( function ( $class ) {
    $prefix = 'QA\\';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    // Remove prefix
    $relative = substr( $class, strlen( $prefix ) );

    // Normalize: QA\Admin\Settings -> Admin/Settings
    $path = str_replace( '\\', '/', $relative );

    // 1) Support PSR-ish paths: includes/Admin/Settings.php (if present)
    $candidate = QA_PLUGIN_DIR . 'includes/' . $path . '.php';
    if ( file_exists( $candidate ) ) {
        require_once $candidate;
        return;
    }

    // 2) Preferred convention:
    //    QA\PostTypes            -> includes/class-post-types.php
    //    QA\Admin\Settings      -> includes/Admin/class-settings.php
    //    QA\Frontend\Templates  -> includes/Frontend/class-templates.php
    $parts = explode( '/', $path );
    $class_name = strtolower( array_pop( $parts ) );
    $dir = implode( '/', $parts );

    if ( $dir !== '' ) {
        $candidate = QA_PLUGIN_DIR . 'includes/' . $dir . '/class-' . $class_name . '.php';
        if ( file_exists( $candidate ) ) {
            require_once $candidate;
            return;
        }
    }

    // 3) Fallback: includes/class-<all-parts-hyphenated>.php
    $slug = strtolower( str_replace( '/', '-', $path ) );
    $candidate = QA_PLUGIN_DIR . 'includes/class-' . $slug . '.php';
    if ( file_exists( $candidate ) ) {
        require_once $candidate;
        return;
    }
} );

// ─── Bootstrap ───────────────────────────────────────────────────────────────
require_once QA_PLUGIN_DIR . 'includes/class-plugin.php';

function qassem_archive() {
    return QA\Plugin::instance();
}
add_action( 'plugins_loaded', 'qassem_archive' );

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook( __FILE__,   [ 'QA\Installer', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'QA\Installer', 'deactivate' ] );
