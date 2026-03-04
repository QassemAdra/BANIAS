/**
 * Qassem Archive — Frontend JS
 * Handles: Filters, Grid/List/Map/Timeline views, Pagination, Leaflet map
 */
(function ($) {
    'use strict';

    const API   = window.qaFrontend?.restUrl || '/wp-json/qassem/v1/';
    const NONCE = window.qaFrontend?.nonce   || '';
    const i18n  = window.qaFrontend?.i18n    || {};
    const ICONS = window.qaFrontend?.typeIcons || {};
    const VER_COLORS = window.qaFrontend?.verColors || {};

    // ─────────────────────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────────────────────
    const state = {
        filters:     {},
        page:        1,
        perPage:     parseInt( window.qaFrontend?.perPage ) || 20,
        totalPages:  1,
        total:       0,
        view:        'grid',
        loading:     false,
        map:         null,
        mapMarkers:  null,
        tlYearFrom:  2010,
        tlYearTo:    new Date().getFullYear(),
    };

    // ─────────────────────────────────────────────────────────────────────
    // Init
    // ─────────────────────────────────────────────────────────────────────
    function init() {
        if ( ! $('#qa-app').length ) return;
        loadFilterOptions();
        bindSearchInput();
        bindFilters();
        bindViewToggle();
        bindMobileFilter();
        bindPaginationEvents();
        bindResetFilters();
        initLocationMaps();
        initEventMaps();

        // Parse URL params
        parseUrlFilters();
        loadEvidence();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Filter Options (populate dropdowns from API)
    // ─────────────────────────────────────────────────────────────────────
    function loadFilterOptions() {
        $.ajax({
            url:    API + 'filter-options',
            method: 'GET',
            beforeSend: h => h.setRequestHeader( 'X-WP-Nonce', NONCE ),
            success: function ( res ) {
                populateLocationSelect( res.locations || [] );
                populateEventSelect(    res.events    || [] );
                populateYearSelect(     res.years     || [] );
                populateTopicsList(     res.topics    || [] );
                updateHeroStats();
            }
        });
    }

    function populateLocationSelect( locs ) {
        const $sel = $( '#filter-location' );
        locs.forEach( l => $sel.append( `<option value="${l.id}">${escHtml(l.title)}</option>` ) );
    }

    function populateEventSelect( events ) {
        const $sel = $( '#filter-event' );
        events.forEach( e => $sel.append( `<option value="${e.id}">${escHtml(e.title)}</option>` ) );
    }

    function populateYearSelect( years ) {
        const $sel = $( '#filter-year' );
        years.forEach( y => $sel.append( `<option value="${y}">${y}</option>` ) );
    }

    function populateTopicsList( topics ) {
        const $list = $( '#filter-topics' );
        topics.filter( t => ! t.parent ).forEach( t => {
            const $item = $( `<div class="qa-topic-item" data-filter="topic" data-value="${t.slug}">${escHtml(t.name)} <span class="qa-topic-count">${t.count}</span></div>` );
            $list.append( $item );
        });
        $list.on( 'click', '.qa-topic-item', function() {
            const $item = $( this );
            $item.toggleClass( 'active' );
            setFilter( 'topic', $item.hasClass( 'active' ) ? $item.data( 'value' ) : '' );
        });
    }

    function updateHeroStats() {
        $.ajax({
            url:    API + 'evidence?per_page=1',
            method: 'GET',
            success: function( res ) {
                $( '#stat-total' ).text( res.total || '—' );
            }
        });
        $.ajax({
            url:    API + 'evidence?per_page=1&verification_level=verified',
            method: 'GET',
            success: function( res ) {
                $( '#stat-verified' ).text( res.total || '—' );
            }
        });
        $.ajax({
            url:    API + 'locations',
            method: 'GET',
            success: function( res ) {
                $( '#stat-locations' ).text( ( res.locations || [] ).length || '—' );
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Search
    // ─────────────────────────────────────────────────────────────────────
    let searchTimer;
    function bindSearchInput() {
        $( '#qa-search-input' ).on( 'input', function() {
            const val = $( this ).val();
            $( '#qa-search-clear' ).toggle( val.length > 0 );
            clearTimeout( searchTimer );
            searchTimer = setTimeout( () => {
                setFilter( 'search', val );
            }, 350 );
        });

        $( '#qa-search-clear' ).on( 'click', function() {
            $( '#qa-search-input' ).val( '' ).trigger( 'focus' );
            $( this ).hide();
            setFilter( 'search', '' );
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Filters
    // ─────────────────────────────────────────────────────────────────────
    function bindFilters() {
        // Select dropdowns
        $( '.qa-filter-select' ).on( 'change', function() {
            setFilter( $( this ).data( 'filter' ), $( this ).val() );
        });

        // Type pills
        $( document ).on( 'click', '.qa-pill[data-filter]', function() {
            const $pill  = $( this );
            const filter = $pill.data( 'filter' );
            const value  = $pill.data( 'value' );

            $pill.closest( '.qa-type-pills' ).find( '.qa-pill' ).removeClass( 'active' );
            $pill.addClass( 'active' );
            setFilter( filter, value );
        });

        // Date range
        $( '.qa-date-input' ).on( 'change', function() {
            setFilter( $( this ).data( 'filter' ), $( this ).val() );
        });
    }

    function setFilter( key, value ) {
        if ( value === '' || value === null || value === undefined ) {
            delete state.filters[ key ];
        } else {
            state.filters[ key ] = value;
        }
        state.page = 1;
        updateActiveChips();
        updateUrl();
        loadEvidence();
    }

    function updateActiveChips() {
        const $wrap = $( '#qa-active-filters' );
        $wrap.empty();

        const labels = {
            evidence_type:      { video: '🎬 فيديو', photo: '📷 صورة', document: '📄 وثيقة', testimony: '💬 شهادة' },
            verification_level: { verified: '✅ محقق', probable: '🔵 غالب', possible: '🟡 محتمل', unverified: '⬜ غير محقق' },
            source_type:        { eyewitness: 'شاهد عيان', activist: 'ناشط', media: 'إعلام', organization: 'منظمة', archive: 'أرشيف' },
        };
        const filterLabels = {
            search: 'بحث', year: 'سنة', date_from: 'من', date_to: 'إلى',
            location_id: 'موقع', event_id: 'حدث', topic: 'موضوع', tag: 'وسم',
            orderby: 'ترتيب',
        };

        Object.entries( state.filters ).forEach( ( [ key, val ] ) => {
            if ( key === 'orderby' ) return; // Don't show orderby as chip
            const displayVal = ( labels[ key ] && labels[ key ][ val ] ) ? labels[ key ][ val ]
                             : ( filterLabels[ key ] ? `${filterLabels[key]}: ${val}` : val );
            const $chip = $( `<div class="qa-active-chip">
                ${escHtml(displayVal)}
                <span class="qa-active-chip-remove" data-filter-key="${key}">✕</span>
            </div>` );
            $wrap.append( $chip );
        });

        $( document ).off( 'click.chip' ).on( 'click.chip', '.qa-active-chip-remove', function() {
            const key = $( this ).data( 'filter-key' );
            delete state.filters[ key ];
            // Reset related UI elements
            $( `[data-filter="${key}"]` ).each( function() {
                if ( $( this ).is( 'select' ) ) $( this ).val( '' );
                if ( $( this ).is( 'input[type="date"]' ) ) $( this ).val( '' );
                if ( $( this ).hasClass( 'qa-pill' ) ) {
                    if ( $( this ).data( 'value' ) === '' ) $( this ).addClass( 'active' );
                    else $( this ).removeClass( 'active' );
                }
                if ( $( this ).hasClass( 'qa-topic-item' ) ) $( this ).removeClass( 'active' );
            });
            state.page = 1;
            updateActiveChips();
            updateUrl();
            loadEvidence();
        });
    }

    function bindResetFilters() {
        $( '#qa-reset-filters' ).on( 'click', function() {
            state.filters = {};
            state.page    = 1;
            $( '.qa-filter-select' ).val( '' );
            $( '.qa-date-input' ).val( '' );
            $( '#qa-search-input' ).val( '' ).trigger( 'input' );
            $( '.qa-pill' ).removeClass( 'active' );
            $( '.qa-pill[data-value=""]' ).addClass( 'active' );
            $( '.qa-topic-item' ).removeClass( 'active' );
            updateActiveChips();
            updateUrl();
            loadEvidence();
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // URL State
    // ─────────────────────────────────────────────────────────────────────
    function parseUrlFilters() {
        const params = new URLSearchParams( window.location.search );
        params.forEach( ( val, key ) => {
            if ( val ) state.filters[ key ] = val;
        });
    }

    function updateUrl() {
        const params = new URLSearchParams( state.filters );
        const url = window.location.pathname + ( params.toString() ? '?' + params.toString() : '' );
        history.replaceState( null, '', url );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Load Evidence
    // ─────────────────────────────────────────────────────────────────────
    function loadEvidence() {
        if ( state.loading ) return;
        state.loading = true;
        showLoader( true );

        const params = Object.assign( {}, state.filters, {
            page:     state.page,
            per_page: state.perPage,
        });

        $.ajax({
            url:    API + 'evidence',
            method: 'GET',
            data:   params,
            beforeSend: h => h.setRequestHeader( 'X-WP-Nonce', NONCE ),
            success: function( res ) {
                state.total      = res.total || 0;
                state.totalPages = res.total_pages || 1;
                renderResults( res.items || [] );
                renderPagination();
                updateResultsCount();
            },
            error: function() {
                showError( 'حدث خطأ أثناء تحميل الأدلة. يرجى المحاولة مجددًا.' );
            },
            complete: function() {
                state.loading = false;
                showLoader( false );
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────────────
    function renderResults( items ) {
        if ( state.view === 'grid' )     renderGrid( items );
        if ( state.view === 'list' )     renderList( items );
        if ( state.view === 'map' )      loadMapPoints();
        if ( state.view === 'timeline' ) loadTimeline();
    }

    function renderGrid( items ) {
        const $grid = $( '#qa-cards-grid' ).empty();
        if ( ! items.length ) { $grid.html( noResultsHtml() ); return; }
        items.forEach( item => $grid.append( buildCard( item ) ) );
    }

    function renderList( items ) {
        const $list = $( '#qa-cards-list' ).empty();
        if ( ! items.length ) { $list.html( noResultsHtml() ); return; }
        items.forEach( item => $list.append( buildListItem( item ) ) );
    }

    function buildCard( item ) {
        const typeClass = `qa-ev-type-${item.evidence_type || 'document'}`;
        const verClass  = verificationClass( item.verification_level );
        const icon      = ICONS[ item.evidence_type ] || '📎';
        const thumbHtml = item.thumb_url
            ? `<img src="${escHtml(item.thumb_url)}" alt="" loading="lazy">`
            : `<span class="qa-ev-card-thumb-icon">${icon}</span>`;

        return `<a href="${escHtml(item.permalink)}" class="qa-ev-card">
            <div class="qa-ev-card-thumb">
                ${thumbHtml}
                <span class="qa-ev-type-strip ${typeClass}">${icon} ${typeLabel(item.evidence_type)}</span>
                <div class="qa-ev-ver-dot qa-ev-ver-dot--${verClass}"></div>
            </div>
            <div class="qa-ev-card-body">
                <h3 class="qa-ev-card-title">${escHtml(item.title)}</h3>
                <div class="qa-ev-card-meta">
                    ${item.event_date_display ? `<span class="qa-ev-card-meta-item">📅 ${escHtml(item.event_date_display)}</span>` : ''}
                    ${item.location_name      ? `<span class="qa-ev-card-meta-item">📍 ${escHtml(item.location_name)}</span>` : ''}
                    ${item.event_name         ? `<span class="qa-ev-card-meta-item">🗓 ${escHtml(item.event_name)}</span>` : ''}
                </div>
            </div>
            <div class="qa-ev-card-footer">
                <span class="qa-ver-badge qa-ver-badge--${verClass}">${verLabel(item.verification_level)}</span>
                <div class="qa-ai-dot ${item.ai_status === 'ready' ? 'qa-ai-dot--ready' : ''}" title="AI: ${item.ai_status || 'idle'}"></div>
            </div>
        </a>`;
    }

    function buildListItem( item ) {
        const icon     = ICONS[ item.evidence_type ] || '📎';
        const verClass = verificationClass( item.verification_level );
        return `<a href="${escHtml(item.permalink)}" class="qa-ev-list-item">
            <div class="qa-ev-list-icon">${icon}</div>
            <div class="qa-ev-list-info">
                <div class="qa-ev-list-title">${escHtml(item.title)}</div>
                <div class="qa-ev-list-sub">
                    ${item.event_date_display ? `<span>📅 ${escHtml(item.event_date_display)}</span>` : ''}
                    ${item.location_name      ? `<span>📍 ${escHtml(item.location_name)}</span>` : ''}
                    ${item.source_type        ? `<span>${sourceLabel(item.source_type)}</span>` : ''}
                </div>
            </div>
            <div class="qa-ev-list-right">
                <span class="qa-ver-badge qa-ver-badge--${verClass}">${verLabel(item.verification_level)}</span>
                ${item.ai_status === 'ready' ? '<div class="qa-ai-dot qa-ai-dot--ready" title="AI مكتمل"></div>' : ''}
            </div>
        </a>`;
    }

    function noResultsHtml() {
        return `<div class="qa-no-results">
            <div class="qa-no-results-icon">🔍</div>
            <h3>لا توجد نتائج</h3>
            <p>جرّب تغيير معايير البحث أو إعادة تعيين الفلاتر</p>
        </div>`;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Map View
    // ─────────────────────────────────────────────────────────────────────
    function initMap() {
        if ( state.map || ! $( '#qa-leaflet-map' ).length ) return;
        if ( typeof L === 'undefined' ) return;

        const lat  = parseFloat( window.qaFrontend?.mapLat  ) || 35.1667;
        const lng  = parseFloat( window.qaFrontend?.mapLng  ) || 35.9333;
        const zoom = parseInt(   window.qaFrontend?.mapZoom ) || 10;

        state.map = L.map( 'qa-leaflet-map' ).setView( [ lat, lng ], zoom );

        L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
            maxZoom: 18,
        }).addTo( state.map );

        // Marker clusters
        state.mapMarkers = L.markerClusterGroup ? L.markerClusterGroup({
            maxClusterRadius: 60,
            showCoverageOnHover: false,
        }) : null;
    }

    function loadMapPoints() {
        initMap();
        if ( ! state.map ) return;

        const params = {};
        [ 'evidence_type', 'verification_level', 'event_id', 'location_id' ].forEach( k => {
            if ( state.filters[ k ] ) params[ k ] = state.filters[ k ];
        });

        $.ajax({
            url:    API + 'map-points',
            method: 'GET',
            data:   params,
            beforeSend: h => h.setRequestHeader( 'X-WP-Nonce', NONCE ),
            success: function( res ) {
                renderMapPoints( res.points || [] );
            }
        });
    }

    function renderMapPoints( points ) {
        if ( ! state.map ) return;

        // Clear old markers
        if ( state.mapMarkers ) {
            state.mapMarkers.clearLayers();
        } else {
            state.map.eachLayer( l => { if ( l instanceof L.Marker ) state.map.removeLayer( l ); } );
        }

        const verColors = VER_COLORS;

        points.forEach( pt => {
            const color   = verColors[ pt.verification_level ] || '#95a5a6';
            const icon    = ICONS[ pt.evidence_type ] || '📎';

            const markerIcon = L.divIcon({
                className: 'qa-map-marker',
                html: `<div style="
                    background:${color};
                    width:30px; height:30px;
                    border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);
                    border:2px solid #fff;
                    box-shadow:0 2px 8px rgba(0,0,0,0.4);
                    display:flex; align-items:center; justify-content:center;
                "><span style="transform:rotate(45deg);font-size:13px;">${icon}</span></div>`,
                iconSize:   [ 30, 30 ],
                iconAnchor: [ 15, 30 ],
            });

            const marker = L.marker( [ pt.lat, pt.lng ], { icon: markerIcon } );
            const popup  = L.popup({ maxWidth: 250, direction: 'rtl' }).setContent( `
                <div class="qa-leaflet-popup">
                    <div class="qa-leaflet-popup-title">${escHtml(pt.title)}</div>
                    <div class="qa-leaflet-popup-meta">
                        ${pt.event_date ? `<span>📅 ${escHtml(pt.event_date)}</span>` : ''}
                        ${pt.location_name ? `<span>📍 ${escHtml(pt.location_name)}</span>` : ''}
                        <span>${verLabel(pt.verification_level)}</span>
                    </div>
                    <a href="${escHtml(pt.permalink)}" class="qa-leaflet-popup-link" target="_blank">عرض الدليل</a>
                </div>
            ` );
            marker.bindPopup( popup );

            if ( state.mapMarkers ) {
                state.mapMarkers.addLayer( marker );
            } else {
                marker.addTo( state.map );
            }
        });

        if ( state.mapMarkers ) state.map.addLayer( state.mapMarkers );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Timeline View
    // ─────────────────────────────────────────────────────────────────────
    function loadTimeline() {
        const params = {
            year_from: state.tlYearFrom,
            year_to:   state.tlYearTo,
        };
        [ 'location_id', 'event_id', 'evidence_type', 'verification_level' ].forEach( k => {
            if ( state.filters[ k ] ) params[ k ] = state.filters[ k ];
        });

        $( '#tl-range-label' ).text( `${state.tlYearFrom} — ${state.tlYearTo}` );

        $.ajax({
            url:    API + 'timeline',
            method: 'GET',
            data:   params,
            beforeSend: h => h.setRequestHeader( 'X-WP-Nonce', NONCE ),
            success: function( res ) {
                renderTimeline( res.grouped || {} );
            }
        });
    }

    function renderTimeline( grouped ) {
        const $track = $( '#qa-timeline-track' ).empty();
        const months = Object.keys( grouped ).sort();

        if ( ! months.length ) {
            $track.html( noResultsHtml() );
            return;
        }

        const monthNames = [ '', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                             'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ];
        const verClassMap = { verified: 'green', probable: 'blue', possible: 'yellow', unverified: 'grey' };

        months.forEach( ym => {
            const [ y, m ] = ym.split( '-' );
            const monthLabel = `${monthNames[ parseInt(m) ] || m} ${y}`;
            const items = grouped[ ym ];

            let itemsHtml = items.map( item => {
                const icon     = ICONS[ item.evidence_type ] || '📎';
                const verClass = verClassMap[ item.verification_level ] || 'grey';
                return `<a href="${escHtml(item.permalink)}" class="qa-tl-ev-card">
                    <div class="qa-tl-ev-icon">${icon}</div>
                    <div class="qa-tl-ev-info">
                        <div class="qa-tl-ev-title">${escHtml(item.title)}</div>
                        <div class="qa-tl-ev-sub">${item.location_name ? '📍 ' + escHtml(item.location_name) : ''}</div>
                    </div>
                    <div class="qa-tl-ev-ver qa-tl-ev-ver--${verClass}"></div>
                </a>`;
            }).join('');

            $track.append( `
                <div class="qa-tl-group">
                    <div class="qa-tl-group-label">
                        <span class="qa-tl-month-label">${monthLabel}</span>
                        <span style="font-size:11px;color:var(--qa-text-dim)">${items.length} دليل</span>
                    </div>
                    <div class="qa-tl-group-items">${itemsHtml}</div>
                </div>
            `);
        });
    }

    // Timeline controls
    $( document ).on( 'click', '#tl-zoom-in', function() {
        state.tlYearFrom = Math.min( state.tlYearFrom + 2, state.tlYearTo );
        state.tlYearTo   = Math.max( state.tlYearTo   - 2, state.tlYearFrom );
        loadTimeline();
    });

    $( document ).on( 'click', '#tl-zoom-out', function() {
        state.tlYearFrom = Math.max( state.tlYearFrom - 2, 2000 );
        state.tlYearTo   = Math.min( state.tlYearTo   + 2, new Date().getFullYear() );
        loadTimeline();
    });

    // ─────────────────────────────────────────────────────────────────────
    // View Toggle
    // ─────────────────────────────────────────────────────────────────────
    function bindViewToggle() {
        $( '.qa-view-btn' ).on( 'click', function() {
            const view = $( this ).data( 'view' );
            $( '.qa-view-btn' ).removeClass( 'active' );
            $( this ).addClass( 'active' );
            $( '.qa-results-view' ).removeClass( 'active' );
            $( '#view-' + view ).addClass( 'active' );
            state.view = view;

            if ( view === 'map' ) {
                loadMapPoints();
                if ( state.map ) setTimeout( () => state.map.invalidateSize(), 100 );
            }
            if ( view === 'timeline' ) loadTimeline();
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Pagination
    // ─────────────────────────────────────────────────────────────────────
    function renderPagination() {
        const $pag = $( '#qa-pagination' ).empty();
        if ( state.totalPages <= 1 ) return;

        const makeBtn = ( label, page, active = false, disabled = false ) => {
            const $btn = $( `<button type="button" class="qa-page-btn${active ? ' active' : ''}">${label}</button>` );
            if ( disabled ) $btn.prop( 'disabled', true );
            else $btn.on( 'click', () => goToPage( page ) );
            return $btn;
        };

        $pag.append( makeBtn( '→', state.page + 1, false, state.page >= state.totalPages ) ); // RTL: next is left

        // Page numbers
        const range = getPageRange( state.page, state.totalPages );
        range.forEach( p => {
            if ( p === '…' ) $pag.append( $( '<span class="qa-page-info">…</span>' ) );
            else $pag.append( makeBtn( p, p, p === state.page ) );
        });

        $pag.append( makeBtn( '←', state.page - 1, false, state.page <= 1 ) ); // RTL: prev is right

        $pag.append( $( `<span class="qa-page-info">صفحة ${state.page} من ${state.totalPages}</span>` ) );
    }

    function getPageRange( current, total ) {
        if ( total <= 7 ) return Array.from( { length: total }, ( _, i ) => i + 1 );
        const pages = [ 1 ];
        if ( current > 3 ) pages.push( '…' );
        for ( let i = Math.max( 2, current - 1 ); i <= Math.min( total - 1, current + 1 ); i++ ) pages.push( i );
        if ( current < total - 2 ) pages.push( '…' );
        pages.push( total );
        return pages;
    }

    function goToPage( page ) {
        state.page = page;
        loadEvidence();
        $( 'html,body' ).animate( { scrollTop: $( '.qa-results-area' ).offset().top - 80 }, 300 );
    }

    function bindPaginationEvents() {} // Event delegation used above

    // ─────────────────────────────────────────────────────────────────────
    // Mobile Filter Toggle
    // ─────────────────────────────────────────────────────────────────────
    function bindMobileFilter() {
        $( '#qa-mobile-filter-btn' ).on( 'click', function() {
            $( '#qa-filters-sidebar' ).addClass( 'mobile-open' );
        });

        $( '#qa-reset-filters' ).on( 'click.mobile', function() {
            $( '#qa-filters-sidebar' ).removeClass( 'mobile-open' );
        });

        $( document ).on( 'click', function( e ) {
            if ( $( e.target ).closest( '#qa-filters-sidebar' ).length === 0 &&
                 $( e.target ).attr( 'id' ) !== 'qa-mobile-filter-btn' ) {
                $( '#qa-filters-sidebar' ).removeClass( 'mobile-open' );
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Location / Event page maps
    // ─────────────────────────────────────────────────────────────────────
    function initLocationMaps() {
        const $el = $( '#qa-location-map' );
        if ( ! $el.length || typeof L === 'undefined' ) return;

        const lat      = parseFloat( $el.data( 'lat' ) );
        const lng      = parseFloat( $el.data( 'lng' ) );
        const locId    = $el.data( 'location-id' );

        const map = L.map( 'qa-location-map' ).setView( [ lat, lng ], 13 );
        L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' } ).addTo( map );
        L.marker( [ lat, lng ] ).addTo( map );

        // Load evidence points for this location
        if ( locId ) {
            $.get( API + 'map-points?location_id=' + locId, function( res ) {
                ( res.points || [] ).forEach( pt => {
                    const icon = ICONS[ pt.evidence_type ] || '📎';
                    L.circleMarker( [ pt.lat, pt.lng ], {
                        radius:      8,
                        fillColor:   VER_COLORS[ pt.verification_level ] || '#95a5a6',
                        fillOpacity: 0.85,
                        color:       '#fff',
                        weight:      2,
                    }).bindPopup( `<a href="${pt.permalink}" style="font-weight:700">${icon} ${escHtml(pt.title)}</a>` ).addTo( map );
                });
            });
        }
    }

    function initEventMaps() {
        const $el = $( '#qa-event-map' );
        if ( ! $el.length || typeof L === 'undefined' ) return;

        const lat     = parseFloat( $el.data( 'lat' ) );
        const lng     = parseFloat( $el.data( 'lng' ) );
        const eventId = $el.data( 'event-id' );

        const map = L.map( 'qa-event-map' ).setView( [ lat, lng ], 12 );
        L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' } ).addTo( map );

        if ( eventId ) {
            $.get( API + 'map-points?event_id=' + eventId, function( res ) {
                ( res.points || [] ).forEach( pt => {
                    L.marker( [ pt.lat, pt.lng ] )
                        .bindPopup( `<a href="${pt.permalink}">${escHtml(pt.title)}</a>` )
                        .addTo( map );
                });
                if ( res.points && res.points.length ) {
                    const group = new L.featureGroup( [] );
                    res.points.forEach( pt => L.marker( [ pt.lat, pt.lng ] ).addTo( group ) );
                    map.fitBounds( group.getBounds().pad( 0.2 ) );
                }
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // UI helpers
    // ─────────────────────────────────────────────────────────────────────
    function showLoader( show ) {
        const $loader = $( '#qa-loader' );
        if ( show ) $loader.removeClass( 'hidden' );
        else $loader.addClass( 'hidden' );
    }

    function showError( msg ) {
        $( '#qa-cards-grid, #qa-cards-list' ).html(
            `<div class="qa-no-results"><div class="qa-no-results-icon">⚠️</div><h3>${escHtml(msg)}</h3></div>`
        );
    }

    function updateResultsCount() {
        $( '#qa-results-count' ).html(
            state.total > 0
                ? `<strong>${state.total}</strong> دليل موثق`
                : `<span style="color:var(--qa-text-dim)">لا توجد نتائج</span>`
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Label helpers
    // ─────────────────────────────────────────────────────────────────────
    function verificationClass( level ) {
        return { verified: 'green', probable: 'blue', possible: 'yellow', unverified: 'grey' }[ level ] || 'grey';
    }

    function verLabel( level ) {
        return { verified: 'محقق', probable: 'غالب', possible: 'محتمل', unverified: 'غير محقق' }[ level ] || level || '';
    }

    function typeLabel( type ) {
        return { video: 'فيديو', photo: 'صورة', document: 'وثيقة', testimony: 'شهادة' }[ type ] || type || '';
    }

    function sourceLabel( src ) {
        return { eyewitness: 'شاهد عيان', activist: 'ناشط', media: 'إعلام', organization: 'منظمة', archive: 'أرشيف' }[ src ] || src || '';
    }

    function escHtml( str ) {
        if ( ! str ) return '';
        return String( str )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Boot
    // ─────────────────────────────────────────────────────────────────────
    $( document ).ready( init );

})(jQuery);
