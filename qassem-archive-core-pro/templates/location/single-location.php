<?php
/**
 * Single Location Template
 */
defined( 'ABSPATH' ) || exit;
get_header();

$id        = get_the_ID();
$lat       = get_post_meta( $id, 'qa_lat',            true );
$lng       = get_post_meta( $id, 'qa_lng',            true );
$precision = get_post_meta( $id, 'qa_geo_precision',  true );
$aliases   = get_post_meta( $id, 'qa_aliases',        true );
$parent_id = (int) get_post_meta( $id, 'qa_parent_location_id', true );

$precision_labels = [ 'city' => 'مدينة', 'district' => 'منطقة', 'neighborhood' => 'حي', 'exact' => 'دقيق', 'unknown' => 'غير محدد' ];

$evidence_q = new WP_Query( [ 'post_type' => 'qa_evidence', 'post_status' => 'publish', 'meta_key' => 'qa_location_id', 'meta_value' => $id, 'posts_per_page' => 20, 'orderby' => 'meta_value', 'order' => 'DESC' ] );
$events_q   = new WP_Query( [ 'post_type' => 'qa_event',    'post_status' => 'publish', 'meta_key' => 'qa_location_id', 'meta_value' => $id, 'posts_per_page' => 20 ] );
?>
<div class="qa-single-location" dir="rtl">

    <nav class="qa-breadcrumb">
        <a href="<?php echo home_url(); ?>">الرئيسية</a>
        <span>/</span>
        <span>المواقع</span>
        <span>/</span>
        <?php if ( $parent_id ) : ?>
            <a href="<?php echo get_permalink( $parent_id ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a>
            <span>/</span>
        <?php endif; ?>
        <span><?php echo esc_html( get_the_title() ); ?></span>
    </nav>

    <header class="qa-location-header">
        <h1>📍 <?php the_title(); ?></h1>
        <?php if ( $aliases ) : ?>
            <p class="qa-location-aliases">يُعرف أيضًا: <?php echo esc_html( $aliases ); ?></p>
        <?php endif; ?>
        <div class="qa-location-meta-row">
            <?php if ( $precision ) : ?>
                <span><?php echo esc_html( $precision_labels[ $precision ] ?? $precision ); ?></span>
            <?php endif; ?>
            <?php if ( $lat && $lng ) : ?>
                <span class="qa-coords"><?php echo esc_html( "{$lat}°N, {$lng}°E" ); ?></span>
            <?php endif; ?>
            <span><?php echo intval( $evidence_q->found_posts ); ?> دليل</span>
        </div>
    </header>

    <!-- Map -->
    <?php if ( $lat && $lng ) : ?>
        <div class="qa-location-map-wrap">
            <div id="qa-location-map" style="height:420px;"
                 data-lat="<?php echo esc_attr( $lat ); ?>"
                 data-lng="<?php echo esc_attr( $lng ); ?>"
                 data-location-id="<?php echo $id; ?>"></div>
        </div>
    <?php endif; ?>

    <!-- Evidence in this location -->
    <?php if ( $evidence_q->have_posts() ) : ?>
        <section class="qa-location-evidence">
            <h2>الأدلة في هذا الموقع (<?php echo intval( $evidence_q->found_posts ); ?>)</h2>
            <div class="qa-location-ev-grid">
                <?php
                $type_icons = [ 'video' => '🎬', 'photo' => '📷', 'document' => '📄', 'testimony' => '💬' ];
                $ver_class  = [ 'unverified' => 'grey', 'possible' => 'yellow', 'probable' => 'blue', 'verified' => 'green' ];
                while ( $evidence_q->have_posts() ) : $evidence_q->the_post();
                    $ev_type  = get_post_meta( get_the_ID(), 'qa_evidence_type', true );
                    $ev_date  = get_post_meta( get_the_ID(), 'qa_event_date', true );
                    $ev_ver   = get_post_meta( get_the_ID(), 'qa_verification_level', true ) ?: 'unverified';
                    $ev_thumb = get_post_meta( get_the_ID(), 'qa_thumb_url', true );
                ?>
                    <a href="<?php the_permalink(); ?>" class="qa-loc-ev-card">
                        <div class="qa-loc-ev-thumb">
                            <?php if ( $ev_thumb ) : ?>
                                <img src="<?php echo esc_url( $ev_thumb ); ?>" alt="" loading="lazy">
                            <?php else : ?>
                                <span><?php echo $type_icons[ $ev_type ] ?? '📎'; ?></span>
                            <?php endif; ?>
                            <div class="qa-loc-ev-ver qa-loc-ev-ver--<?php echo esc_attr( $ver_class[ $ev_ver ] ?? 'grey' ); ?>"></div>
                        </div>
                        <div class="qa-loc-ev-info">
                            <h4><?php the_title(); ?></h4>
                            <?php if ( $ev_date ) : ?><span><?php echo esc_html( $ev_date ); ?></span><?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php if ( $evidence_q->max_num_pages > 1 ) : ?>
                <a href="<?php echo get_post_type_archive_link( 'qa_evidence' ) . '?location_id=' . $id; ?>" class="qa-btn qa-btn--ghost">
                    عرض جميع الأدلة ←
                </a>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- Events in this location -->
    <?php if ( $events_q->have_posts() ) : ?>
        <section class="qa-location-events">
            <h2>الأحداث في هذا الموقع</h2>
            <ul class="qa-events-list">
                <?php while ( $events_q->have_posts() ) : $events_q->the_post();
                    $e_start = get_post_meta( get_the_ID(), 'qa_start_date', true );
                ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <span class="qa-ev-list-title"><?php the_title(); ?></span>
                            <?php if ( $e_start ) : ?><span class="qa-ev-list-date"><?php echo esc_html( $e_start ); ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </section>
    <?php endif; ?>

</div>
<?php get_footer(); ?>
