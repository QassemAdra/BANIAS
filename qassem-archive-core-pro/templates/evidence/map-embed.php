<?php
/**
 * Map Embed Template — used by [qa_map] shortcode
 */
defined( 'ABSPATH' ) || exit;
$map_height = $map_height ?? '500px';
$map_event  = $map_event  ?? 0;
?>
<div class="qa-map-embed" dir="rtl">
    <div id="qa-leaflet-map"
         style="height:<?php echo esc_attr( $map_height ); ?>;"
         <?php if ( $map_event ) : ?>data-filter-event="<?php echo absint( $map_event ); ?>"<?php endif; ?>>
    </div>
</div>
