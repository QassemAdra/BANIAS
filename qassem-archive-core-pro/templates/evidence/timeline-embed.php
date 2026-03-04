<?php
/**
 * Timeline Embed Template — used by [qa_timeline] shortcode
 */
defined( 'ABSPATH' ) || exit;
$year_from  = $a['year_from']   ?? '2000';
$year_to    = $a['year_to']     ?? date( 'Y' );
$loc_filter = $a['location_id'] ?? '';
?>
<div class="qa-timeline-embed" dir="rtl"
     data-year-from="<?php echo absint( $year_from ); ?>"
     data-year-to="<?php echo absint( $year_to ); ?>"
     <?php if ( $loc_filter ) : ?>data-location-id="<?php echo absint( $loc_filter ); ?>"<?php endif; ?>>
    <div class="qa-timeline-controls">
        <button type="button" class="qa-btn-ghost" id="tl-zoom-in">+ تكبير</button>
        <button type="button" class="qa-btn-ghost" id="tl-zoom-out">− تصغير</button>
        <span class="qa-tl-range-label" id="tl-range-label">
            <?php echo absint( $year_from ); ?> — <?php echo absint( $year_to ); ?>
        </span>
    </div>
    <div id="qa-timeline-track" class="qa-timeline-track"></div>
</div>
