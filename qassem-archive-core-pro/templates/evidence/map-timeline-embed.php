<?php
/**
 * Template: Map + Timeline Widget
 * Shortcode: [qa_map_and_timeline]
 *
 * Attributes:
 *  map_height   — height of the map pane, default 650px
 *  year_from    — earliest year, default 2000
 *  year_to      — latest year, default current year
 *  event_id     — pre-filter by event ID
 *  title        — widget heading (optional)
 *  subtitle     — widget sub-heading (optional)
 */
defined( 'ABSPATH' ) || exit;

// atts are passed from shortcode via $sc_atts variable
$height   = esc_attr( $sc_atts['map_height'] ?? '650px' );
$title    = esc_html( $sc_atts['title']    ?? '' );
$subtitle = esc_html( $sc_atts['subtitle'] ?? '' );
?>
<div class="qamt-widget" dir="rtl"
     data-year-from="<?php echo absint( $sc_atts['year_from'] ?? 2000 ); ?>"
     data-year-to="<?php echo absint( $sc_atts['year_to'] ?? date('Y') ); ?>"
     data-event-id="<?php echo absint( $sc_atts['event_id'] ?? 0 ); ?>"
     style="--qamt-map-h:<?php echo $height; ?>">

    <?php if ( $title ) : ?>
        <header class="qamt-widget-header">
            <h2 class="qamt-widget-title"><?php echo $title; ?></h2>
            <?php if ( $subtitle ) : ?>
                <p class="qamt-widget-subtitle"><?php echo $subtitle; ?></p>
            <?php endif; ?>
        </header>
    <?php endif; ?>

    <!-- Mobile tabs (visible < 768px via CSS) -->
    <div class="qamt-mobile-tabs">
        <button type="button" class="qamt-tab-btn active" data-tab="map">🗺 الخريطة</button>
        <button type="button" class="qamt-tab-btn" data-tab="timeline">📅 الخط الزمني</button>
    </div>

    <!-- Widget root — JS builds the rest -->
    <!-- JS will call initWidget() on .qamt-widget -->

</div>
