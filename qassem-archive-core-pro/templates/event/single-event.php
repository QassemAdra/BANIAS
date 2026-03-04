<?php
/**
 * Single Event Template
 */
defined( 'ABSPATH' ) || exit;
get_header();

$id         = get_the_ID();
$start_date = get_post_meta( $id, 'qa_start_date',    true );
$end_date   = get_post_meta( $id, 'qa_end_date',      true );
$loc_id     = (int) get_post_meta( $id, 'qa_location_id', true );
$summary    = get_post_meta( $id, 'qa_event_summary', true );
$topics     = wp_get_post_terms( $id, 'qa_topic', [ 'fields' => 'all' ] );

function qa_ev_date( string $d ): string {
    if ( ! $d ) return '';
    $m = [ '', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ];
    $ts = strtotime( $d );
    return $ts ? (int) date( 'd', $ts ) . ' ' . $m[ (int) date( 'm', $ts ) ] . ' ' . date( 'Y', $ts ) : $d;
}

// Get related evidence
$evidence_q = new WP_Query( [
    'post_type'      => 'qa_evidence',
    'post_status'    => 'publish',
    'meta_key'       => 'qa_event_id',
    'meta_value'     => $id,
    'posts_per_page' => 50,
    'meta_query'     => [ [ 'key' => 'qa_event_date', 'compare' => 'EXISTS' ] ],
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
] );
?>
<div class="qa-single-event" dir="rtl">

    <nav class="qa-breadcrumb">
        <a href="<?php echo home_url(); ?>">الرئيسية</a>
        <span>/</span>
        <span>الأحداث</span>
        <span>/</span>
        <span><?php echo esc_html( get_the_title() ); ?></span>
    </nav>

    <header class="qa-event-header">
        <div class="qa-event-dates">
            <?php if ( $start_date ) : ?>
                <span class="qa-event-date-range">
                    📅 <?php echo esc_html( qa_ev_date( $start_date ) ); ?>
                    <?php if ( $end_date && $end_date !== $start_date ) : ?>
                        — <?php echo esc_html( qa_ev_date( $end_date ) ); ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
            <?php if ( $loc_id ) : ?>
                <span class="qa-event-location">📍 <a href="<?php echo get_permalink( $loc_id ); ?>"><?php echo esc_html( get_the_title( $loc_id ) ); ?></a></span>
            <?php endif; ?>
        </div>
        <h1><?php the_title(); ?></h1>
        <?php if ( $summary ) : ?>
            <p class="qa-event-summary"><?php echo esc_html( $summary ); ?></p>
        <?php endif; ?>
        <div class="qa-event-badge">
            <span><?php echo intval( $evidence_q->found_posts ); ?> دليل موثق</span>
        </div>
    </header>

    <?php if ( $evidence_q->have_posts() ) : ?>
        <!-- Evidence Timeline -->
        <section class="qa-event-timeline">
            <h2>الأدلة المرتبطة — خط زمني</h2>
            <div class="qa-event-tl-track">
                <?php
                $type_icons = [ 'video' => '🎬', 'photo' => '📷', 'document' => '📄', 'testimony' => '💬' ];
                $ver_class  = [ 'unverified' => 'grey', 'possible' => 'yellow', 'probable' => 'blue', 'verified' => 'green' ];
                while ( $evidence_q->have_posts() ) : $evidence_q->the_post();
                    $ev_type  = get_post_meta( get_the_ID(), 'qa_evidence_type', true );
                    $ev_date  = get_post_meta( get_the_ID(), 'qa_event_date', true );
                    $ev_ver   = get_post_meta( get_the_ID(), 'qa_verification_level', true ) ?: 'unverified';
                    $ev_thumb = get_post_meta( get_the_ID(), 'qa_thumb_url', true );
                ?>
                    <div class="qa-tl-item">
                        <div class="qa-tl-dot qa-tl-dot--<?php echo esc_attr( $ver_class[ $ev_ver ] ?? 'grey' ); ?>">
                            <?php echo $type_icons[ $ev_type ] ?? '📎'; ?>
                        </div>
                        <div class="qa-tl-content">
                            <?php if ( $ev_date ) : ?><span class="qa-tl-date"><?php echo esc_html( qa_ev_date( $ev_date ) ); ?></span><?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="qa-tl-title"><?php the_title(); ?></a>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Map if location has coordinates -->
    <?php if ( $loc_id ) :
        $lat = get_post_meta( $loc_id, 'qa_lat', true );
        $lng = get_post_meta( $loc_id, 'qa_lng', true );
        if ( $lat && $lng ) : ?>
            <section class="qa-event-map-section">
                <h2>الموقع الجغرافي</h2>
                <div id="qa-event-map" style="height:350px;"
                     data-lat="<?php echo esc_attr( $lat ); ?>"
                     data-lng="<?php echo esc_attr( $lng ); ?>"
                     data-event-id="<?php echo $id; ?>"></div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ( $topics && ! is_wp_error( $topics ) ) : ?>
        <div class="qa-event-topics">
            <?php foreach ( $topics as $t ) : ?>
                <a href="<?php echo get_term_link( $t ); ?>" class="qa-term-badge qa-topic-badge"><?php echo esc_html( $t->name ); ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
<?php get_footer(); ?>
