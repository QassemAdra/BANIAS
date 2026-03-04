<?php
namespace QA\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Loads custom templates for qa_evidence, qa_event, qa_location
 * and registers shortcodes + assets for the front-end.
 *
 * Phase 3: adds [qa_map_and_timeline] shortcode + enqueues
 *          map-timeline.js / map-timeline.css
 */
class Templates {

    public function register(): void {
        add_filter( 'template_include',   [ $this, 'load_template'       ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets'      ] );
        add_action( 'init',               [ $this, 'register_shortcodes' ] );
    }

    // ─── Template loader ─────────────────────────────────────────────────
    public function load_template( string $template ): string {

        if ( is_singular( 'qa_evidence' ) ) {
            return $this->locate( 'evidence/single-evidence.php', $template );
        }
        if ( is_singular( 'qa_event' ) ) {
            return $this->locate( 'event/single-event.php', $template );
        }
        if ( is_singular( 'qa_location' ) ) {
            return $this->locate( 'location/single-location.php', $template );
        }
        if ( is_post_type_archive( 'qa_evidence' ) ) {
            return $this->locate( 'evidence/archive-evidence.php', $template );
        }

        return $template;
    }

    private function locate( string $file, string $fallback ): string {
        $theme_file  = get_stylesheet_directory() . '/qassem-archive/' . $file;
        $plugin_file = QA_PLUGIN_DIR . 'templates/' . $file;

        if ( file_exists( $theme_file ) )  return $theme_file;
        if ( file_exists( $plugin_file ) ) return $plugin_file;
        return $fallback;
    }

    // ─── Assets ───────────────────────────────────────────────────────────
    public function enqueue_assets(): void {
        $is_qa = is_singular( [ 'qa_evidence', 'qa_event', 'qa_location' ] )
              || is_post_type_archive( 'qa_evidence' )
              || $this->page_has_shortcode();

        if ( ! $is_qa ) return;

        $settings = get_option( 'qa_settings', [] );

        // ── Leaflet ──────────────────────────────────────────────────────
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [], '1.9.4'
        );
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [], '1.9.4', true
        );

        // ── MarkerCluster ─────────────────────────────────────────────────
        wp_enqueue_style(
            'leaflet-cluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css',
            [ 'leaflet' ], '1.5.3'
        );
        wp_enqueue_script(
            'leaflet-cluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
            [ 'leaflet' ], '1.5.3', true
        );

        // ── Archive / Single CSS+JS (Phase 1+2) ───────────────────────────
        wp_enqueue_style(
            'qa-frontend',
            QA_PLUGIN_URL . 'assets/css/frontend.css',
            [ 'leaflet', 'leaflet-cluster' ],
            QA_VERSION
        );
        wp_enqueue_script(
            'qa-frontend',
            QA_PLUGIN_URL . 'assets/js/frontend.js',
            [ 'jquery', 'leaflet', 'leaflet-cluster' ],
            QA_VERSION,
            true
        );

        // Shared localisation for archive / single pages
        wp_localize_script( 'qa-frontend', 'qaFrontend', $this->frontend_data( $settings ) );

        // ── Map+Timeline widget (Phase 3) ─────────────────────────────────
        if ( $this->page_has_shortcode( 'qa_map_and_timeline' ) ) {
            wp_enqueue_style(
                'qa-map-timeline',
                QA_PLUGIN_URL . 'assets/css/map-timeline.css',
                [ 'leaflet', 'leaflet-cluster' ],
                QA_VERSION
            );
            wp_enqueue_script(
                'qa-map-timeline',
                QA_PLUGIN_URL . 'assets/js/map-timeline.js',
                [ 'jquery', 'leaflet', 'leaflet-cluster' ],
                QA_VERSION,
                true
            );
            wp_localize_script( 'qa-map-timeline', 'qaMapTimeline', [
                'restUrl' => rest_url( 'qassem/v1/' ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
                'map'     => [
                    'lat'  => $settings['default_map_lat']  ?? '35.1667',
                    'lng'  => $settings['default_map_lng']  ?? '35.9333',
                    'zoom' => $settings['default_map_zoom'] ?? '10',
                ],
                'opts' => [
                    'yearFrom' => apply_filters( 'qa_timeline_year_from', 2010 ),
                    'yearTo'   => apply_filters( 'qa_timeline_year_to',   (int) date('Y') ),
                ],
            ] );
        }
    }

    private function frontend_data( array $settings ): array {
        return [
            'restUrl'   => rest_url( 'qassem/v1/' ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'mapLat'    => $settings['default_map_lat']  ?? '35.1667',
            'mapLng'    => $settings['default_map_lng']  ?? '35.9333',
            'mapZoom'   => $settings['default_map_zoom'] ?? '10',
            'perPage'   => 20,
            'i18n'      => [
                'loading'           => 'جارٍ التحميل…',
                'noResults'         => 'لا توجد نتائج',
                'loadMore'          => 'تحميل المزيد',
                'filterReset'       => 'إعادة تعيين',
                'evidenceCount'     => 'دليل',
                'viewDetails'       => 'عرض التفاصيل',
                'close'             => 'إغلاق',
                'allTypes'          => 'كل الأنواع',
                'allLocations'      => 'كل المواقع',
                'allEvents'         => 'كل الأحداث',
                'allYears'          => 'كل السنوات',
                'allVerification'   => 'كل الدرجات',
                'allSources'        => 'كل المصادر',
                'searchPlaceholder' => 'بحث في الأدلة…',
            ],
            'typeIcons' => [
                'video'     => '🎬',
                'photo'     => '📷',
                'document'  => '📄',
                'testimony' => '💬',
            ],
            'verColors' => [
                'verified'   => '#2ecc71',
                'probable'   => '#3498db',
                'possible'   => '#f39c12',
                'unverified' => '#95a5a6',
            ],
        ];
    }

    // ─── Shortcodes ───────────────────────────────────────────────────────
    public function register_shortcodes(): void {
        add_shortcode( 'qa_archive',          [ $this, 'shortcode_archive'          ] );
        add_shortcode( 'qa_map',              [ $this, 'shortcode_map'              ] );
        add_shortcode( 'qa_timeline',         [ $this, 'shortcode_timeline'         ] );
        add_shortcode( 'qa_map_and_timeline', [ $this, 'shortcode_map_and_timeline' ] );
    }

    public function shortcode_archive( array $atts ): string {
        ob_start();
        include QA_PLUGIN_DIR . 'templates/evidence/archive-evidence.php';
        return ob_get_clean();
    }

    public function shortcode_map( array $atts ): string {
        $a = shortcode_atts( [ 'height' => '500px', 'event_id' => '' ], $atts );
        ob_start();
        $map_height = esc_attr( $a['height'] );
        $map_event  = absint( $a['event_id'] );
        include QA_PLUGIN_DIR . 'templates/evidence/map-embed.php';
        return ob_get_clean();
    }

    public function shortcode_timeline( array $atts ): string {
        $a = shortcode_atts( [
            'year_from'   => '2000',
            'year_to'     => date('Y'),
            'location_id' => '',
        ], $atts );
        ob_start();
        include QA_PLUGIN_DIR . 'templates/evidence/timeline-embed.php';
        return ob_get_clean();
    }

    /**
     * [qa_map_and_timeline] — Phase 3 combined widget
     *
     * @param array $atts {
     *   map_height  string  CSS height of map pane, default '650px'
     *   year_from   int     Start year for timeline, default 2000
     *   year_to     int     End year for timeline, default current year
     *   event_id    int     Pre-filter by event, default 0
     *   title       string  Optional widget heading
     *   subtitle    string  Optional sub-heading
     * }
     */
    public function shortcode_map_and_timeline( array $atts ): string {
        $sc_atts = shortcode_atts( [
            'map_height' => '650px',
            'year_from'  => '2000',
            'year_to'    => date('Y'),
            'event_id'   => '0',
            'title'      => '',
            'subtitle'   => '',
        ], $atts );

        ob_start();
        include QA_PLUGIN_DIR . 'templates/evidence/map-timeline-embed.php';
        return ob_get_clean();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Check if the current page contains any QA shortcode.
     * Pass a specific shortcode tag to check only that one.
     */
    private function page_has_shortcode( string $tag = '' ): bool {
        global $post;
        if ( ! $post ) return false;

        $all = [ 'qa_archive', 'qa_map', 'qa_timeline', 'qa_map_and_timeline' ];
        $check = $tag ? [ $tag ] : $all;

        foreach ( $check as $sc ) {
            if ( has_shortcode( $post->post_content, $sc ) ) return true;
        }
        return false;
    }
}
