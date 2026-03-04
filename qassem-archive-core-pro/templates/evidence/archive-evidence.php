<?php
/**
 * Archive Template: Evidence
 * Shortcode: [qa_archive]
 * WP Archive: /evidence/
 */
defined( 'ABSPATH' ) || exit;

$is_shortcode = ! is_post_type_archive( 'qa_evidence' );
if ( ! $is_shortcode ) get_header();
?>
<div class="qa-archive-root" dir="rtl" id="qa-app">

    <!-- ═══ HERO ══════════════════════════════════════════════════════════ -->
    <header class="qa-archive-hero">
        <div class="qa-hero-inner">
            <div class="qa-hero-text">
                <div class="qa-hero-eyebrow">أرشيف الأدلة التاريخية</div>
                <h1 class="qa-hero-title">قاسم أرشيف</h1>
                <p class="qa-hero-sub">توثيق وأرشفة الأدلة التاريخية — بانياس والساحل السوري</p>
            </div>
            <div class="qa-hero-stats" id="qa-hero-stats">
                <div class="qa-stat"><span class="qa-stat-num" id="stat-total">—</span><span class="qa-stat-label">دليل موثق</span></div>
                <div class="qa-stat"><span class="qa-stat-num" id="stat-verified">—</span><span class="qa-stat-label">دليل محقق</span></div>
                <div class="qa-stat"><span class="qa-stat-num" id="stat-locations">—</span><span class="qa-stat-label">موقع</span></div>
            </div>
        </div>
    </header>

    <!-- ═══ SEARCH BAR ═══════════════════════════════════════════════════ -->
    <div class="qa-search-bar">
        <div class="qa-search-inner">
            <div class="qa-search-wrap">
                <span class="qa-search-icon">🔍</span>
                <input type="text" id="qa-search-input" class="qa-search-input"
                       placeholder="بحث في الأدلة، الأحداث، المواقع…"
                       autocomplete="off">
                <button type="button" id="qa-search-clear" class="qa-search-clear" style="display:none">✕</button>
            </div>
            <div class="qa-view-toggle">
                <button type="button" class="qa-view-btn active" data-view="grid" title="شبكة">⊞</button>
                <button type="button" class="qa-view-btn" data-view="list" title="قائمة">☰</button>
                <button type="button" class="qa-view-btn" data-view="map"  title="خريطة">🗺</button>
                <button type="button" class="qa-view-btn" data-view="timeline" title="خط زمني">📅</button>
            </div>
        </div>
    </div>

    <!-- ═══ MAIN LAYOUT ══════════════════════════════════════════════════ -->
    <div class="qa-archive-layout">

        <!-- ── FILTERS SIDEBAR ──────────────────────────────────────────── -->
        <aside class="qa-filters-sidebar" id="qa-filters-sidebar">
            <div class="qa-filters-header">
                <h2 class="qa-filters-title">🔎 تصفية النتائج</h2>
                <button type="button" id="qa-reset-filters" class="qa-btn-ghost">إعادة تعيين</button>
            </div>

            <!-- Active filters chips -->
            <div class="qa-active-filters" id="qa-active-filters"></div>

            <!-- Type -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">نوع الدليل</label>
                <div class="qa-type-pills" id="filter-type-pills">
                    <button type="button" class="qa-pill active" data-filter="evidence_type" data-value="">الكل</button>
                    <button type="button" class="qa-pill" data-filter="evidence_type" data-value="video">🎬 فيديو</button>
                    <button type="button" class="qa-pill" data-filter="evidence_type" data-value="photo">📷 صورة</button>
                    <button type="button" class="qa-pill" data-filter="evidence_type" data-value="document">📄 وثيقة</button>
                    <button type="button" class="qa-pill" data-filter="evidence_type" data-value="testimony">💬 شهادة</button>
                </div>
            </div>

            <!-- Verification -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">درجة التحقق</label>
                <select class="qa-filter-select" data-filter="verification_level" id="filter-verification">
                    <option value="">كل الدرجات</option>
                    <option value="verified">✅ محقق</option>
                    <option value="probable">🔵 غالب</option>
                    <option value="possible">🟡 محتمل</option>
                    <option value="unverified">⬜ غير محقق</option>
                </select>
            </div>

            <!-- Location -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">الموقع</label>
                <select class="qa-filter-select" data-filter="location_id" id="filter-location">
                    <option value="">كل المواقع</option>
                </select>
            </div>

            <!-- Event -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">الحدث</label>
                <select class="qa-filter-select" data-filter="event_id" id="filter-event">
                    <option value="">كل الأحداث</option>
                </select>
            </div>

            <!-- Year -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">السنة</label>
                <select class="qa-filter-select" data-filter="year" id="filter-year">
                    <option value="">كل السنوات</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="qa-filter-group qa-filter-group--daterange">
                <label class="qa-filter-label">نطاق التاريخ</label>
                <div class="qa-date-range">
                    <input type="date" class="qa-date-input" data-filter="date_from" id="filter-date-from" placeholder="من">
                    <span>—</span>
                    <input type="date" class="qa-date-input" data-filter="date_to"   id="filter-date-to"   placeholder="إلى">
                </div>
            </div>

            <!-- Source -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">المصدر</label>
                <select class="qa-filter-select" data-filter="source_type" id="filter-source">
                    <option value="">كل المصادر</option>
                    <option value="eyewitness">شاهد عيان</option>
                    <option value="activist">ناشط</option>
                    <option value="media">وسائل إعلام</option>
                    <option value="organization">منظمة</option>
                    <option value="archive">أرشيف</option>
                </select>
            </div>

            <!-- Topics -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">الموضوع</label>
                <div class="qa-topics-list" id="filter-topics"></div>
            </div>

            <!-- Sort -->
            <div class="qa-filter-group">
                <label class="qa-filter-label">ترتيب حسب</label>
                <select class="qa-filter-select" data-filter="orderby" id="filter-orderby">
                    <option value="event_date">تاريخ الحدث</option>
                    <option value="title">العنوان</option>
                    <option value="date">تاريخ النشر</option>
                </select>
            </div>
        </aside>

        <!-- ── RESULTS AREA ──────────────────────────────────────────────── -->
        <main class="qa-results-area">

            <!-- Results header -->
            <div class="qa-results-header">
                <div class="qa-results-count" id="qa-results-count">
                    <span class="qa-spinner qa-spinner--inline"></span>
                </div>
                <button type="button" class="qa-mobile-filter-toggle" id="qa-mobile-filter-btn">
                    ⚙ تصفية
                </button>
            </div>

            <!-- Grid View -->
            <div class="qa-grid-view qa-results-view active" id="view-grid">
                <div class="qa-cards-grid" id="qa-cards-grid"></div>
            </div>

            <!-- List View -->
            <div class="qa-list-view qa-results-view" id="view-list">
                <div class="qa-cards-list" id="qa-cards-list"></div>
            </div>

            <!-- Map View -->
            <div class="qa-map-view qa-results-view" id="view-map">
                <div id="qa-leaflet-map" style="height:550px;"></div>
            </div>

            <!-- Timeline View -->
            <div class="qa-timeline-view qa-results-view" id="view-timeline">
                <div class="qa-timeline-controls">
                    <button type="button" class="qa-btn-ghost" id="tl-zoom-in">+ تكبير</button>
                    <button type="button" class="qa-btn-ghost" id="tl-zoom-out">− تصغير</button>
                    <span class="qa-tl-range-label" id="tl-range-label"></span>
                </div>
                <div id="qa-timeline-track" class="qa-timeline-track"></div>
            </div>

            <!-- Loader -->
            <div class="qa-loader" id="qa-loader">
                <div class="qa-loader-dots"><span></span><span></span><span></span></div>
                <p>جارٍ تحميل الأدلة…</p>
            </div>

            <!-- Pagination -->
            <div class="qa-pagination" id="qa-pagination"></div>

        </main>
    </div><!-- /.qa-archive-layout -->

</div><!-- /#qa-app -->
<?php if ( ! $is_shortcode ) get_footer(); ?>
