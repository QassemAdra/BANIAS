<?php
/**
 * Single Evidence Template — Phase 2 Enhanced
 */
defined( 'ABSPATH' ) || exit;
get_header();
global $post;
$id = $post->ID;

$type        = get_post_meta( $id, 'qa_evidence_type',         true );
$event_date  = get_post_meta( $id, 'qa_event_date',            true );
$date_prec   = get_post_meta( $id, 'qa_event_date_precision',  true ) ?: 'exact';
$loc_id      = (int) get_post_meta( $id, 'qa_location_id',     true );
$evt_id      = (int) get_post_meta( $id, 'qa_event_id',        true );
$source_type = get_post_meta( $id, 'qa_source_type',           true );
$source_url  = get_post_meta( $id, 'qa_source_url',            true );
$ver_level   = get_post_meta( $id, 'qa_verification_level',    true ) ?: 'unverified';
$ver_notes   = get_post_meta( $id, 'qa_verification_notes',    true );
$ver_by      = (int) get_post_meta( $id, 'qa_verification_by', true );
$ver_date    = get_post_meta( $id, 'qa_verification_date',     true );
$media_url   = get_post_meta( $id, 'qa_media_url',             true );
$media_stor  = get_post_meta( $id, 'qa_media_storage',         true );
$thumb_url   = get_post_meta( $id, 'qa_thumb_url',             true );
$duration    = get_post_meta( $id, 'qa_file_duration',         true );
$file_size   = get_post_meta( $id, 'qa_file_size',             true );
$language    = get_post_meta( $id, 'qa_language',              true );
$ai_status   = get_post_meta( $id, 'qa_ai_status',             true ) ?: 'idle';
$ai_summary  = get_post_meta( $id, 'qa_ai_summary',            true );
$ai_tags     = json_decode( get_post_meta( $id, 'qa_ai_tags', true ) ?: '[]', true );
$ai_entities = json_decode( get_post_meta( $id, 'qa_ai_entities', true ) ?: '{}', true );
$transcript  = get_post_meta( $id, 'qa_ai_transcript',         true );
$ocr_text    = get_post_meta( $id, 'qa_ai_ocr_text',           true );

$type_labels   = ['video'=>'فيديو','photo'=>'صورة','document'=>'وثيقة','testimony'=>'شهادة'];
$type_icons    = ['video'=>'🎬','photo'=>'📷','document'=>'📄','testimony'=>'💬'];
$source_labels = ['eyewitness'=>'شاهد عيان','activist'=>'ناشط','media'=>'وسائل إعلام','organization'=>'منظمة','archive'=>'أرشيف'];
$ver_labels    = ['unverified'=>'غير محقق','possible'=>'محتمل','probable'=>'غالب','verified'=>'محقق'];
$ver_class     = ['unverified'=>'grey','possible'=>'yellow','probable'=>'blue','verified'=>'green'];
$lang_labels   = ['ar'=>'العربية','en'=>'الإنجليزية','fr'=>'الفرنسية'];

$topics = wp_get_post_terms( $id, 'qa_topic', ['fields'=>'all'] );
$tags   = wp_get_post_terms( $id, 'qa_tag',   ['fields'=>'all'] );

if ( ! function_exists('qa_date_ar') ) {
    function qa_date_ar( string $d, string $p = 'exact' ): string {
        if ( ! $d ) return '';
        $m = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $ts = strtotime($d);
        if (!$ts) return $d;
        if ($p === 'year')  return date('Y', $ts);
        if ($p === 'month') return $m[(int)date('m',$ts)].' '.date('Y',$ts);
        return (int)date('d',$ts).' '.$m[(int)date('m',$ts)].' '.date('Y',$ts);
    }
}
?>
<div class="qa-single-evidence" dir="rtl" id="qa-app">

<nav class="qa-breadcrumb">
    <a href="<?php echo home_url(); ?>">الرئيسية</a><span>/</span>
    <a href="<?php echo get_post_type_archive_link('qa_evidence'); ?>">الأدلة</a><span>/</span>
    <span><?php echo esc_html(get_the_title()); ?></span>
</nav>

<header class="qa-ev-header">
    <div class="qa-ev-header-badges">
        <div class="qa-ev-type-badge qa-ev-type-<?php echo esc_attr($type); ?>">
            <?php echo $type_icons[$type] ?? '📎'; ?> <?php echo esc_html($type_labels[$type] ?? $type); ?>
        </div>
        <span class="qa-verification-badge qa-ver-<?php echo esc_attr($ver_class[$ver_level] ?? 'grey'); ?>">
            <?php echo esc_html($ver_labels[$ver_level] ?? $ver_level); ?>
        </span>
        <?php if ($ai_status === 'ready') : ?><span class="qa-ai-ready-badge">🤖 AI</span><?php endif; ?>
    </div>
    <h1 class="qa-ev-title"><?php the_title(); ?></h1>
    <div class="qa-ev-meta-row">
        <?php if ($event_date) : ?><span class="qa-ev-meta-item">📅 <?php echo esc_html(qa_date_ar($event_date,$date_prec)); ?></span><?php endif; ?>
        <?php if ($loc_id) : ?><span class="qa-ev-meta-item">📍 <a href="<?php echo get_permalink($loc_id); ?>"><?php echo esc_html(get_the_title($loc_id)); ?></a></span><?php endif; ?>
        <?php if ($evt_id) : ?><span class="qa-ev-meta-item">🗓 <a href="<?php echo get_permalink($evt_id); ?>"><?php echo esc_html(get_the_title($evt_id)); ?></a></span><?php endif; ?>
    </div>
</header>

<div class="qa-ev-body">
    <section class="qa-ev-media">
        <?php if ($media_url && $type === 'video') : ?>
            <div class="qa-video-wrap">
                <video controls preload="metadata" class="qa-video-player" <?php echo $thumb_url ? 'poster="'.esc_url($thumb_url).'"' : ''; ?>>
                    <source src="<?php echo esc_url($media_url); ?>" type="video/mp4">
                    <?php _e('متصفحك لا يدعم تشغيل الفيديو.', QA_TEXT_DOMAIN); ?>
                </video>
                <div class="qa-video-meta-bar">
                    <?php if ($duration) : ?><span>⏱ <?php echo esc_html($duration); ?></span><?php endif; ?>
                    <?php if ($file_size) : ?><span>💾 <?php echo esc_html($file_size); ?></span><?php endif; ?>
                    <a href="<?php echo esc_url($media_url); ?>" target="_blank" rel="noopener" class="qa-media-download-link">⬇ تحميل</a>
                </div>
            </div>
        <?php elseif ($media_url && $type === 'photo') : ?>
            <div class="qa-photo-wrap">
                <img src="<?php echo esc_url($media_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="qa-evidence-photo" loading="lazy">
                <div class="qa-photo-actions">
                    <a href="<?php echo esc_url($media_url); ?>" target="_blank" rel="noopener" class="qa-btn qa-btn--ghost qa-btn--sm">🔍 الحجم الكامل</a>
                </div>
            </div>
        <?php elseif ($media_url && $type === 'document') : ?>
            <div class="qa-document-wrap">
                <div class="qa-doc-icon">📄</div>
                <p class="qa-doc-label">وثيقة مرفقة</p>
                <?php if ($file_size) : ?><p class="qa-doc-size">💾 <?php echo esc_html($file_size); ?></p><?php endif; ?>
                <div class="qa-doc-actions">
                    <a href="<?php echo esc_url($media_url); ?>" class="qa-btn qa-btn--primary" target="_blank" rel="noopener">⬇ تحميل الوثيقة</a>
                </div>
                <?php if (strtolower(substr($media_url,-4)) === '.pdf') : ?>
                    <div class="qa-pdf-embed" style="margin-top:16px;">
                        <iframe src="<?php echo esc_url($media_url); ?>" width="100%" height="600" style="border:none;border-radius:8px;"></iframe>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($type === 'testimony') : ?>
            <div class="qa-testimony-wrap">
                <div style="font-size:56px;margin-bottom:16px;">💬</div>
                <p class="qa-testimony-label">شهادة</p>
                <?php if ($media_url) : ?>
                    <a href="<?php echo esc_url($media_url); ?>" class="qa-btn qa-btn--ghost" target="_blank" rel="noopener">▶ استماع للتسجيل</a>
                <?php endif; ?>
            </div>
        <?php elseif ($thumb_url) : ?>
            <div class="qa-photo-wrap"><img src="<?php echo esc_url($thumb_url); ?>" alt="" class="qa-evidence-photo" loading="lazy"></div>
        <?php else : ?>
            <div class="qa-no-media"><span><?php echo $type_icons[$type] ?? '📎'; ?></span><p>لا توجد وسائط مرفقة</p></div>
        <?php endif; ?>
    </section>

    <aside class="qa-ev-meta-panel">
        <div class="qa-meta-card">
            <h3 class="qa-meta-card-title">📋 بيانات الدليل</h3>
            <dl class="qa-meta-dl">
                <dt>نوع الدليل</dt><dd><?php echo esc_html($type_labels[$type] ?? $type); ?></dd>
                <?php if ($event_date) : ?><dt>تاريخ الحدث</dt><dd><?php echo esc_html(qa_date_ar($event_date,$date_prec)); ?></dd><?php endif; ?>
                <?php if ($loc_id) : ?><dt>الموقع</dt><dd><a href="<?php echo get_permalink($loc_id); ?>"><?php echo esc_html(get_the_title($loc_id)); ?></a></dd><?php endif; ?>
                <?php if ($evt_id) : ?><dt>الحدث</dt><dd><a href="<?php echo get_permalink($evt_id); ?>"><?php echo esc_html(get_the_title($evt_id)); ?></a></dd><?php endif; ?>
                <?php if ($source_type) : ?><dt>المصدر</dt><dd><?php echo esc_html($source_labels[$source_type] ?? $source_type); ?></dd><?php endif; ?>
                <?php if ($source_url) : ?><dt>المصدر الأصلي</dt><dd><a href="<?php echo esc_url($source_url); ?>" target="_blank" rel="noopener" class="qa-external-link">رابط ↗</a></dd><?php endif; ?>
                <?php if ($language) : ?><dt>اللغة</dt><dd><?php echo esc_html($lang_labels[$language] ?? $language); ?></dd><?php endif; ?>
            </dl>
        </div>

        <div class="qa-meta-card qa-ver-card qa-ver-card--<?php echo esc_attr($ver_class[$ver_level] ?? 'grey'); ?>">
            <h3 class="qa-meta-card-title">✅ التحقق</h3>
            <div class="qa-ver-level-big"><?php echo esc_html($ver_labels[$ver_level] ?? $ver_level); ?></div>
            <?php if ($ver_notes) : ?><p class="qa-ver-notes"><?php echo esc_html($ver_notes); ?></p><?php endif; ?>
            <?php if ($ver_by && $ver_date) :
                $vname = ($u = get_userdata($ver_by)) ? $u->display_name : 'مجهول'; ?>
                <div class="qa-ver-meta"><small>بواسطة: <?php echo esc_html($vname); ?> — <?php echo esc_html(date_i18n('j F Y', strtotime($ver_date))); ?></small></div>
            <?php endif; ?>
        </div>

        <?php if ($topics && !is_wp_error($topics) && count($topics)) : ?>
            <div class="qa-meta-card">
                <h3 class="qa-meta-card-title">🏷 المواضيع</h3>
                <div class="qa-terms-wrap">
                    <?php foreach ($topics as $t) : ?>
                        <a href="<?php echo get_term_link($t); ?>" class="qa-term-badge qa-topic-badge"><?php echo esc_html($t->name); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tags && !is_wp_error($tags) && count($tags)) : ?>
            <div class="qa-meta-card">
                <h3 class="qa-meta-card-title">🔖 الوسوم</h3>
                <div class="qa-terms-wrap">
                    <?php foreach ($tags as $t) : ?>
                        <a href="<?php echo get_term_link($t); ?>" class="qa-term-badge qa-tag-badge">#<?php echo esc_html($t->name); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <a href="<?php echo get_post_type_archive_link('qa_evidence'); ?>" class="qa-btn qa-btn--ghost" style="width:100%;text-align:center;display:block;">← الأرشيف</a>
    </aside>
</div>

<?php if ($post->post_content) : ?>
<section class="qa-ev-description">
    <h2>الوصف</h2>
    <div class="qa-ev-content"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<?php if ($ai_status === 'ready' && ($ai_summary || $ai_tags || $transcript || $ocr_text || !empty($ai_entities))) : ?>
<section class="qa-ai-insights">
    <div class="qa-ai-badge">🤖 تحليل AI — اقتراحات تحتاج مراجعة بشرية — AI لا يُحدد هويات أشخاص</div>
    <?php if ($ai_summary) : ?><div class="qa-ai-block"><h3>الملخص</h3><p><?php echo esc_html($ai_summary); ?></p></div><?php endif; ?>
    <?php if (is_array($ai_tags) && count($ai_tags)) : ?>
        <div class="qa-ai-block"><h3>كلمات مفتاحية مقترحة</h3><div class="qa-ai-tags">
            <?php foreach ($ai_tags as $t) : $ts = is_string($t) ? $t : ''; if ($ts) : ?><span class="qa-ai-tag-pill"><?php echo esc_html($ts); ?></span><?php endif; endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if (!empty($ai_entities['places'])) : ?>
        <div class="qa-ai-block"><h3>أماكن مذكورة (مقترحة)</h3><div class="qa-ai-tags">
            <?php foreach ((array)$ai_entities['places'] as $pl) : ?><span class="qa-ai-entity-pill">📍 <?php echo esc_html($pl); ?></span><?php endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if (!empty($ai_entities['dates'])) : ?>
        <div class="qa-ai-block"><h3>تواريخ مذكورة (مقترحة)</h3><div class="qa-ai-tags">
            <?php foreach ((array)$ai_entities['dates'] as $d) : ?><span class="qa-ai-entity-pill">📅 <?php echo esc_html($d); ?></span><?php endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if ($transcript) : ?><details class="qa-ai-block qa-ai-expandable"><summary>نص تفريغ الصوت <span class="qa-ai-badge-sm">AI</span></summary><div class="qa-transcript-text"><?php echo nl2br(esc_html($transcript)); ?></div></details><?php endif; ?>
    <?php if ($ocr_text) : ?><details class="qa-ai-block qa-ai-expandable"><summary>نص OCR <span class="qa-ai-badge-sm">AI</span></summary><div class="qa-ocr-text"><?php echo nl2br(esc_html($ocr_text)); ?></div></details><?php endif; ?>
</section>
<?php endif; ?>

<?php
$rq = ($evt_id||$loc_id) ? new WP_Query(['post_type'=>'qa_evidence','post_status'=>'publish','posts_per_page'=>4,'post__not_in'=>[$id],'meta_query'=>array_filter(['relation'=>'OR',$evt_id?['key'=>'qa_event_id','value'=>$evt_id,'type'=>'NUMERIC']:null,$loc_id?['key'=>'qa_location_id','value'=>$loc_id,'type'=>'NUMERIC']:null])]) : null;
if ($rq && $rq->have_posts()) : ?>
<section class="qa-related-evidence">
    <h2>أدلة ذات صلة</h2>
    <div class="qa-related-grid">
        <?php while ($rq->have_posts()) : $rq->the_post();
            $rt=$type_icons[get_post_meta(get_the_ID(),'qa_evidence_type',true)]??'📎';
            $rv=$ver_class[get_post_meta(get_the_ID(),'qa_verification_level',true)?:'unverified']??'grey';
            $rth=get_post_meta(get_the_ID(),'qa_thumb_url',true);
            $rd=get_post_meta(get_the_ID(),'qa_event_date',true); ?>
            <a href="<?php the_permalink(); ?>" class="qa-related-card">
                <div class="qa-related-thumb">
                    <?php if ($rth) : ?><img src="<?php echo esc_url($rth); ?>" alt="" loading="lazy"><?php else : ?><span class="qa-related-icon"><?php echo $rt; ?></span><?php endif; ?>
                    <div class="qa-ver-dot qa-ver-dot--<?php echo $rv; ?>"></div>
                </div>
                <div class="qa-related-info"><h4><?php the_title(); ?></h4><?php if ($rd) : ?><span class="qa-related-date"><?php echo esc_html(qa_date_ar($rd)); ?></span><?php endif; ?></div>
            </a>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
</section>
<?php endif; ?>

</div>
<?php get_footer(); ?>
