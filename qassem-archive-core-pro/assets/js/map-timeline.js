/**
 * Qassem Archive — Map + Timeline Module (Phase 3)
 *
 * Standalone widget powering [qa_map_and_timeline] shortcode.
 * Dependencies: jQuery, Leaflet 1.9.x, leaflet.markercluster
 *
 * Architecture:
 *   QaMapTimeline.init(rootEl, opts)
 *   ├── FilterBar    — type, verification, event, year, search chips
 *   ├── MapView      — Leaflet + MarkerCluster, synced to filters
 *   └── TimelineView — lightweight JS-built vertical timeline with zoom
 */

/* global L, jQuery, qaMapTimeline */
(function ($) {
    'use strict';

    const API       = window.qaMapTimeline?.restUrl  || '/wp-json/qassem/v1/';
    const NONCE     = window.qaMapTimeline?.nonce     || '';
    const MAP_CFG   = window.qaMapTimeline?.map       || {};
    const OPTS      = window.qaMapTimeline?.opts      || {};

    // ═════════════════════════════════════════════════════════════════════════
    // Constants / lookups
    // ═════════════════════════════════════════════════════════════════════════

    const ICONS = { video:'🎬', photo:'📷', document:'📄', testimony:'💬' };

    const VER = {
        verified:   { color:'#2ecc71', label:'محقق' },
        probable:   { color:'#3498db', label:'غالب' },
        possible:   { color:'#f39c12', label:'محتمل' },
        unverified: { color:'#7f8c8d', label:'غير محقق' },
    };

    const TYPE_COLORS = {
        video:     '#e74c3c',
        photo:     '#9b59b6',
        document:  '#3498db',
        testimony: '#27ae60',
    };

    const AR_MONTHS = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو',
                       'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

    // ═════════════════════════════════════════════════════════════════════════
    // Shared state
    // ═════════════════════════════════════════════════════════════════════════

    const state = {
        filters:        {},
        filterOptions:  null,
        mapData:        [],       // full /map-points result (cached)
        timelineData:   [],       // full /timeline items (cached)
        tlYearFrom:     parseInt(OPTS.yearFrom) || 2010,
        tlYearTo:       parseInt(OPTS.yearTo)   || new Date().getFullYear(),
        tlZoom:         0,        // 0 = months, 1 = quarters, 2 = years
        loading:        { map: false, timeline: false },
        map:            null,
        clusterGroup:   null,
        activePopupId:  null,
        syncHighlight:  null,     // item id highlighted across views
    };

    // ═════════════════════════════════════════════════════════════════════════
    // Utility
    // ═════════════════════════════════════════════════════════════════════════

    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function dateAr(d, precision) {
        if (!d) return '';
        const ts = new Date(d);
        if (isNaN(ts)) return d;
        const day = ts.getUTCDate();
        const mon = AR_MONTHS[ts.getUTCMonth() + 1];
        const yr  = ts.getUTCFullYear();
        if (precision === 'year')  return String(yr);
        if (precision === 'month') return `${mon} ${yr}`;
        return `${day} ${mon} ${yr}`;
    }

    function verClass(level) {
        return { verified:'green', probable:'blue', possible:'yellow', unverified:'grey' }[level] || 'grey';
    }

    function apiGet(endpoint, params) {
        return $.ajax({
            url:  API + endpoint,
            method: 'GET',
            data: params || {},
            beforeSend: h => h.setRequestHeader('X-WP-Nonce', NONCE),
        });
    }

    // Build API params from current filters (omits page/per_page)
    function filterParams() {
        const p = {};
        const allowed = ['evidence_type','verification_level','location_id','event_id','year','date_from','date_to','search'];
        allowed.forEach(k => { if (state.filters[k]) p[k] = state.filters[k]; });
        return p;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Filter Bar
    // ═════════════════════════════════════════════════════════════════════════

    const FilterBar = {
        $root: null,

        build($container) {
            this.$root = $container;
            const html = `
            <div class="qamt-filterbar" dir="rtl">
                <div class="qamt-filterbar-inner">

                    <!-- Search -->
                    <div class="qamt-search-wrap">
                        <span class="qamt-search-icon">🔍</span>
                        <input type="text" class="qamt-search-input" id="qamt-search"
                               placeholder="بحث في العناوين…" autocomplete="off">
                        <button type="button" class="qamt-search-clear" id="qamt-search-clear" style="display:none">✕</button>
                    </div>

                    <!-- Type pills -->
                    <div class="qamt-pills" id="qamt-type-pills">
                        <button type="button" class="qamt-pill active" data-f="evidence_type" data-v="">الكل</button>
                        <button type="button" class="qamt-pill" data-f="evidence_type" data-v="video">🎬 فيديو</button>
                        <button type="button" class="qamt-pill" data-f="evidence_type" data-v="photo">📷 صورة</button>
                        <button type="button" class="qamt-pill" data-f="evidence_type" data-v="document">📄 وثيقة</button>
                        <button type="button" class="qamt-pill" data-f="evidence_type" data-v="testimony">💬 شهادة</button>
                    </div>

                    <!-- Dropdowns -->
                    <div class="qamt-selects">
                        <select class="qamt-select" data-f="verification_level" id="qamt-ver">
                            <option value="">كل درجات التحقق</option>
                            <option value="verified">✅ محقق</option>
                            <option value="probable">🔵 غالب</option>
                            <option value="possible">🟡 محتمل</option>
                            <option value="unverified">⬜ غير محقق</option>
                        </select>
                        <select class="qamt-select" data-f="event_id" id="qamt-event">
                            <option value="">كل الأحداث</option>
                        </select>
                        <select class="qamt-select" data-f="year" id="qamt-year">
                            <option value="">كل السنوات</option>
                        </select>
                    </div>

                    <!-- Active chips + reset -->
                    <div class="qamt-chips-row">
                        <div class="qamt-chips" id="qamt-chips"></div>
                        <button type="button" class="qamt-reset" id="qamt-reset">↺ إعادة تعيين</button>
                    </div>

                </div>
            </div>`;
            $container.html(html);
            this.bindEvents();
        },

        populate(opts) {
            // Events
            const $ev = $('#qamt-event');
            (opts.events || []).forEach(e => {
                $ev.append(`<option value="${esc(e.id)}">${esc(e.title)}</option>`);
            });
            // Years
            const $yr = $('#qamt-year');
            (opts.years || []).forEach(y => {
                $yr.append(`<option value="${esc(y)}">${esc(y)}</option>`);
            });
        },

        bindEvents() {
            let searchTimer;

            // Search
            $('#qamt-search').on('input', function() {
                const v = $(this).val();
                $('#qamt-search-clear').toggle(v.length > 0);
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => setFilter('search', v), 350);
            });
            $('#qamt-search-clear').on('click', function() {
                $('#qamt-search').val('');
                $(this).hide();
                setFilter('search', '');
            });

            // Pills
            $(document).on('click', '.qamt-pill[data-f]', function() {
                const $p = $(this);
                $p.closest('.qamt-pills').find('.qamt-pill').removeClass('active');
                $p.addClass('active');
                setFilter($p.data('f'), $p.data('v'));
            });

            // Selects
            $(document).on('change', '.qamt-select[data-f]', function() {
                setFilter($(this).data('f'), $(this).val());
            });

            // Reset
            $('#qamt-reset').on('click', resetFilters);
        },

        renderChips() {
            const $chips = $('#qamt-chips').empty();
            const labelMaps = {
                evidence_type:      { video:'🎬 فيديو', photo:'📷 صورة', document:'📄 وثيقة', testimony:'💬 شهادة' },
                verification_level: { verified:'✅ محقق', probable:'🔵 غالب', possible:'🟡 محتمل', unverified:'⬜ غير محقق' },
            };
            const keyLabels = { search:'بحث', year:'سنة', event_id:'حدث', location_id:'موقع', date_from:'من', date_to:'إلى' };
            Object.entries(state.filters).forEach(([k, v]) => {
                const display = (labelMaps[k] && labelMaps[k][v]) ? labelMaps[k][v]
                              : keyLabels[k] ? `${keyLabels[k]}: ${v}` : v;
                $chips.append(
                    `<div class="qamt-chip">${esc(display)}<span class="qamt-chip-x" data-key="${esc(k)}">✕</span></div>`
                );
            });
            $(document).off('click.chips').on('click.chips', '.qamt-chip-x', function() {
                clearFilter($(this).data('key'));
            });
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // Filter logic
    // ═════════════════════════════════════════════════════════════════════════

    function setFilter(key, value) {
        if (value === '' || value === null || value === undefined) {
            delete state.filters[key];
        } else {
            state.filters[key] = value;
        }
        FilterBar.renderChips();
        refresh();
    }

    function clearFilter(key) {
        delete state.filters[key];
        // Reset UI element
        $(`[data-f="${key}"]`).each(function() {
            if ($(this).is('select')) $(this).val('');
            if ($(this).hasClass('qamt-pill')) {
                if (!$(this).data('v')) $(this).addClass('active');
                else $(this).removeClass('active');
            }
        });
        $(`#qamt-search`).val('');
        $('#qamt-search-clear').hide();
        FilterBar.renderChips();
        refresh();
    }

    function resetFilters() {
        state.filters = {};
        $('.qamt-select').val('');
        $('.qamt-pill').removeClass('active');
        $('.qamt-pill[data-v=""]').addClass('active');
        $('#qamt-search').val('');
        $('#qamt-search-clear').hide();
        FilterBar.renderChips();
        refresh();
    }

    // Refresh both views after filter change
    function refresh() {
        // Invalidate cached data
        state.mapData      = [];
        state.timelineData = [];
        MapView.load();
        TimelineView.load();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Map View
    // ═════════════════════════════════════════════════════════════════════════

    const MapView = {
        $container: null,
        initialized: false,

        build($container) {
            this.$container = $container;
            $container.html(`
                <div class="qamt-map-wrap">
                    <div class="qamt-map-toolbar">
                        <div class="qamt-map-legend" id="qamt-map-legend"></div>
                        <div class="qamt-map-count" id="qamt-map-count">—</div>
                    </div>
                    <div id="qamt-leaflet" class="qamt-leaflet-map"></div>
                    <div class="qamt-map-loader hidden" id="qamt-map-loader">
                        <div class="qamt-spinner"></div><span>جارٍ تحميل الخريطة…</span>
                    </div>
                </div>
            `);
            this.initLeaflet();
            this.buildLegend();
        },

        initLeaflet() {
            if (this.initialized || typeof L === 'undefined') return;

            const lat  = parseFloat(MAP_CFG.lat)  || 35.1667;
            const lng  = parseFloat(MAP_CFG.lng)  || 35.9333;
            const zoom = parseInt(MAP_CFG.zoom)   || 10;

            state.map = L.map('qamt-leaflet', {
                center: [lat, lng],
                zoom,
                zoomControl: true,
                attributionControl: true,
            });

            // Base layer: OSM
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(state.map);

            // Cluster group
            if (L.markerClusterGroup) {
                state.clusterGroup = L.markerClusterGroup({
                    maxClusterRadius:    70,
                    showCoverageOnHover: false,
                    spiderfyOnMaxZoom:   true,
                    disableClusteringAtZoom: 16,
                    iconCreateFunction(cluster) {
                        const count = cluster.getChildCount();
                        const size  = count < 10 ? 'sm' : count < 50 ? 'md' : 'lg';
                        return L.divIcon({
                            html: `<div class="qamt-cluster qamt-cluster--${size}">${count}</div>`,
                            className: '',
                            iconSize: L.point(40, 40),
                        });
                    },
                });
                state.map.addLayer(state.clusterGroup);
            }

            // Fix size after container renders
            setTimeout(() => state.map && state.map.invalidateSize(), 200);

            this.initialized = true;
        },

        buildLegend() {
            const html = Object.entries(VER).map(([k, v]) =>
                `<span class="qamt-legend-item">
                    <span class="qamt-legend-dot" style="background:${v.color}"></span>
                    ${esc(v.label)}
                </span>`
            ).join('');
            $('#qamt-map-legend').html(html);
        },

        load() {
            if (state.loading.map) return;
            state.loading.map = true;
            this.showLoader(true);

            apiGet('map-points', filterParams()).then(res => {
                state.mapData = res.points || [];
                this.render(state.mapData);
            }).fail(() => {
                this.showError();
            }).always(() => {
                state.loading.map = false;
                this.showLoader(false);
            });
        },

        render(points) {
            if (!state.map) return;

            // Clear markers
            if (state.clusterGroup) {
                state.clusterGroup.clearLayers();
            } else {
                state.map.eachLayer(l => { if (l instanceof L.Marker || l instanceof L.CircleMarker) state.map.removeLayer(l); });
            }

            if (!points.length) {
                $('#qamt-map-count').text('لا توجد نقاط');
                return;
            }

            $('#qamt-map-count').html(`<strong>${points.length}</strong> دليل على الخريطة`);

            const markerLayer = state.clusterGroup || state.map;
            const bounds = [];

            points.forEach(pt => {
                if (!pt.lat || !pt.lng) return;

                const color = (VER[pt.verification_level] || VER.unverified).color;
                const icon  = ICONS[pt.evidence_type] || '📎';

                const markerIcon = L.divIcon({
                    className: 'qamt-marker-wrap',
                    html: `<div class="qamt-marker" style="--mc:${color}" data-id="${pt.id}">
                               <div class="qamt-marker-icon"><span>${icon}</span></div>
                               <div class="qamt-marker-pin"></div>
                           </div>`,
                    iconSize:   [36, 44],
                    iconAnchor: [18, 44],
                    popupAnchor:[0, -44],
                });

                const marker = L.marker([pt.lat, pt.lng], { icon: markerIcon, title: pt.title });

                // Popup
                const verInfo = VER[pt.verification_level] || VER.unverified;
                marker.bindPopup(L.popup({
                    maxWidth: 280,
                    className: 'qamt-popup',
                    autoPan: true,
                }).setContent(`
                    <div class="qamt-popup-card" dir="rtl">
                        ${pt.thumb_url
                            ? `<div class="qamt-popup-thumb"><img src="${esc(pt.thumb_url)}" alt="" loading="lazy"></div>`
                            : `<div class="qamt-popup-thumb qamt-popup-thumb--icon"><span>${icon}</span></div>`
                        }
                        <div class="qamt-popup-body">
                            <div class="qamt-popup-type">${icon} ${esc(pt.evidence_type ? (pt.evidence_type==='video'?'فيديو':pt.evidence_type==='photo'?'صورة':pt.evidence_type==='document'?'وثيقة':'شهادة') : '')}</div>
                            <div class="qamt-popup-title">${esc(pt.title)}</div>
                            <div class="qamt-popup-meta">
                                ${pt.event_date ? `<span>📅 ${esc(dateAr(pt.event_date))}</span>` : ''}
                                ${pt.location_name ? `<span>📍 ${esc(pt.location_name)}</span>` : ''}
                                <span class="qamt-popup-ver" style="color:${verInfo.color}">● ${verInfo.label}</span>
                            </div>
                            <a href="${esc(pt.permalink)}" class="qamt-popup-link" target="_blank" rel="noopener">
                                عرض الدليل ←
                            </a>
                        </div>
                    </div>
                `), { offset: L.point(0, -8) });

                // Hover highlight synced with timeline
                marker.on('popupopen', () => {
                    state.activePopupId = pt.id;
                    TimelineView.highlightItem(pt.id);
                });
                marker.on('popupclose', () => {
                    state.activePopupId = null;
                    TimelineView.clearHighlight();
                });

                if (state.clusterGroup) {
                    state.clusterGroup.addLayer(marker);
                } else {
                    marker.addTo(state.map);
                }
                bounds.push([pt.lat, pt.lng]);
            });

            // Fit map to points if first load
            if (bounds.length && !state.filters._mapFitted) {
                try {
                    state.map.fitBounds(L.latLngBounds(bounds).pad(0.15));
                    state.filters._mapFitted = true;
                } catch(e) {}
            }

            // Invalidate after cluster renders
            setTimeout(() => state.map && state.map.invalidateSize(), 100);
        },

        // Highlight a specific marker on the map (from timeline click)
        focusPoint(id) {
            const pt = state.mapData.find(p => p.id === id);
            if (!pt || !state.map) return;

            state.map.setView([pt.lat, pt.lng], Math.max(state.map.getZoom(), 13), { animate: true });

            // Find and open the marker's popup
            if (state.clusterGroup) {
                const layers = state.clusterGroup.getLayers();
                const marker = layers.find(m => {
                    const el = m.getElement && m.getElement();
                    return el && el.querySelector(`[data-id="${id}"]`);
                });
                if (marker) {
                    state.clusterGroup.zoomToShowLayer(marker, () => marker.openPopup());
                }
            }
        },

        showLoader(show) {
            $('#qamt-map-loader').toggleClass('hidden', !show);
        },

        showError() {
            $('#qamt-map-count').text('⚠ خطأ في تحميل الخريطة');
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // Timeline View — lightweight vertical timeline
    // ═════════════════════════════════════════════════════════════════════════

    const TimelineView = {
        $container: null,
        $track:     null,

        build($container) {
            this.$container = $container;
            $container.html(`
                <div class="qamt-tl-wrap">
                    <div class="qamt-tl-toolbar">
                        <div class="qamt-tl-nav">
                            <button type="button" class="qamt-tl-btn" id="qamt-tl-zoom-in" title="تكبير الفترة">🔍+</button>
                            <button type="button" class="qamt-tl-btn" id="qamt-tl-zoom-out" title="تصغير الفترة">🔍−</button>
                            <button type="button" class="qamt-tl-btn" id="qamt-tl-prev" title="السابق">←</button>
                            <button type="button" class="qamt-tl-btn" id="qamt-tl-next" title="التالي">→</button>
                        </div>
                        <div class="qamt-tl-range" id="qamt-tl-range">
                            ${state.tlYearFrom} — ${state.tlYearTo}
                        </div>
                        <div class="qamt-tl-count" id="qamt-tl-count">—</div>
                    </div>

                    <!-- Year ruler -->
                    <div class="qamt-tl-ruler" id="qamt-tl-ruler"></div>

                    <!-- Track -->
                    <div class="qamt-tl-track" id="qamt-tl-track">
                        <div class="qamt-tl-axis"></div>
                        <div class="qamt-tl-items" id="qamt-tl-items"></div>
                    </div>

                    <div class="qamt-tl-loader hidden" id="qamt-tl-loader">
                        <div class="qamt-spinner"></div><span>جارٍ تحميل الخط الزمني…</span>
                    </div>
                </div>
            `);
            this.$track = $('#qamt-tl-items');
            this.bindNav();
        },

        bindNav() {
            const self = this;

            $('#qamt-tl-zoom-in').on('click', () => {
                const span = state.tlYearTo - state.tlYearFrom;
                if (span <= 2) return; // can't zoom more
                const mid = Math.round((state.tlYearFrom + state.tlYearTo) / 2);
                state.tlYearFrom = Math.max(state.tlYearFrom + 1, mid - 1);
                state.tlYearTo   = Math.min(state.tlYearTo - 1, mid + 1);
                state.tlZoom = Math.min(state.tlZoom + 1, 2);
                self.updateRangeLabel();
                self.load();
            });

            $('#qamt-tl-zoom-out').on('click', () => {
                state.tlYearFrom = Math.max(state.tlYearFrom - 3, 1900);
                state.tlYearTo   = Math.min(state.tlYearTo + 3, new Date().getFullYear() + 1);
                state.tlZoom = Math.max(state.tlZoom - 1, 0);
                self.updateRangeLabel();
                self.load();
            });

            $('#qamt-tl-prev').on('click', () => {
                const span = state.tlYearTo - state.tlYearFrom;
                state.tlYearFrom = Math.max(state.tlYearFrom - span, 1900);
                state.tlYearTo   = state.tlYearFrom + span;
                self.updateRangeLabel();
                self.load();
            });

            $('#qamt-tl-next').on('click', () => {
                const span    = state.tlYearTo - state.tlYearFrom;
                const now     = new Date().getFullYear();
                state.tlYearTo   = Math.min(state.tlYearTo + span, now + 1);
                state.tlYearFrom = state.tlYearTo - span;
                self.updateRangeLabel();
                self.load();
            });
        },

        updateRangeLabel() {
            $('#qamt-tl-range').text(`${state.tlYearFrom} — ${state.tlYearTo}`);
        },

        load() {
            if (state.loading.timeline) return;
            state.loading.timeline = true;
            this.showLoader(true);

            const params = Object.assign(filterParams(), {
                year_from: state.tlYearFrom,
                year_to:   state.tlYearTo,
            });

            apiGet('timeline', params).then(res => {
                state.timelineData = res.items || [];
                this.render(res.grouped || {}, res.items || []);
            }).fail(() => {
                this.showError();
            }).always(() => {
                state.loading.timeline = false;
                this.showLoader(false);
            });
        },

        render(grouped, items) {
            const $items = $('#qamt-tl-items').empty();
            const $ruler = $('#qamt-tl-ruler').empty();

            $('#qamt-tl-count').html(
                items.length
                    ? `<strong>${items.length}</strong> دليل`
                    : '<span style="color:var(--qamt-text-dim)">لا توجد نتائج</span>'
            );

            if (!items.length) {
                $items.html(`
                    <div class="qamt-tl-empty">
                        <div style="font-size:36px">📅</div>
                        <p>لا توجد أدلة في هذه الفترة الزمنية</p>
                    </div>
                `);
                return;
            }

            // Build ruler (years)
            const years = [];
            for (let y = state.tlYearFrom; y <= state.tlYearTo; y++) years.push(y);
            years.forEach(y => {
                const hasData = items.some(i => i.event_date && i.event_date.startsWith(String(y)));
                $ruler.append(`
                    <div class="qamt-ruler-year ${hasData ? 'has-data' : ''}">${y}</div>
                `);
            });

            // Group by zoom level
            const groupKeys = Object.keys(grouped).sort();
            const zoom = state.tlZoom;

            if (zoom >= 2) {
                // Year grouping
                const byYear = {};
                items.forEach(item => {
                    const yr = item.event_date ? item.event_date.slice(0,4) : '?';
                    if (!byYear[yr]) byYear[yr] = [];
                    byYear[yr].push(item);
                });
                Object.keys(byYear).sort().forEach(yr => {
                    this.renderGroup($items, yr, byYear[yr], true);
                });
            } else if (zoom === 1) {
                // Quarter grouping
                const byQ = {};
                items.forEach(item => {
                    if (!item.event_date) return;
                    const yr = item.event_date.slice(0,4);
                    const mo = parseInt(item.event_date.slice(5,7));
                    const q  = Math.ceil(mo/3);
                    const key = `${yr}-Q${q}`;
                    if (!byQ[key]) byQ[key] = [];
                    byQ[key].push(item);
                });
                Object.keys(byQ).sort().forEach(key => {
                    const [yr, q] = key.split('-');
                    const label = `${q} — ${yr}`;
                    this.renderGroup($items, label, byQ[key]);
                });
            } else {
                // Month grouping (default)
                groupKeys.forEach(ym => {
                    const [y, m] = ym.split('-');
                    const label = `${AR_MONTHS[parseInt(m)]} ${y}`;
                    this.renderGroup($items, label, grouped[ym]);
                });
            }
        },

        renderGroup($parent, label, items, isYear = false) {
            const $group = $(`
                <div class="qamt-tl-group">
                    <div class="qamt-tl-group-label ${isYear ? 'is-year' : ''}">
                        <span class="qamt-tl-group-name">${esc(label)}</span>
                        <span class="qamt-tl-group-count">${items.length}</span>
                    </div>
                    <div class="qamt-tl-group-items"></div>
                </div>
            `);
            const $gi = $group.find('.qamt-tl-group-items');

            items.forEach(item => {
                const verInfo = VER[item.verification_level] || VER.unverified;
                const icon    = ICONS[item.evidence_type] || '📎';
                const $card   = $(`
                    <div class="qamt-tl-card" data-id="${item.id}" tabindex="0"
                         role="link" aria-label="${esc(item.title)}">
                        <div class="qamt-tl-card-dot" style="--dc:${verInfo.color}"></div>
                        <div class="qamt-tl-card-icon">${icon}</div>
                        <div class="qamt-tl-card-info">
                            <div class="qamt-tl-card-title">${esc(item.title)}</div>
                            <div class="qamt-tl-card-meta">
                                ${item.event_date ? `<span>📅 ${esc(dateAr(item.event_date))}</span>` : ''}
                                ${item.location_name ? `<span>📍 ${esc(item.location_name)}</span>` : ''}
                                <span class="qamt-tl-ver-label" style="color:${verInfo.color}">${verInfo.label}</span>
                            </div>
                        </div>
                        <a href="${esc(item.permalink)}" class="qamt-tl-card-link"
                           target="_blank" rel="noopener" title="فتح الدليل">←</a>
                    </div>
                `);

                // Click card → focus map + open popup
                $card.on('click', function(e) {
                    if ($(e.target).hasClass('qamt-tl-card-link') || $(e.target).closest('.qamt-tl-card-link').length) return;
                    MapView.focusPoint(item.id);
                    TimelineView.highlightItem(item.id);
                });

                // Keyboard enter
                $card.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        MapView.focusPoint(item.id);
                    }
                });

                $gi.append($card);
            });

            $parent.append($group);
        },

        highlightItem(id) {
            $('.qamt-tl-card').removeClass('highlighted');
            $(`.qamt-tl-card[data-id="${id}"]`).addClass('highlighted')
                .get(0)?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        },

        clearHighlight() {
            $('.qamt-tl-card').removeClass('highlighted');
        },

        showLoader(show) {
            $('#qamt-tl-loader').toggleClass('hidden', !show);
        },

        showError() {
            $('#qamt-tl-items').html('<div class="qamt-tl-empty"><p>⚠ خطأ في تحميل الخط الزمني</p></div>');
        },
    };

    // ═════════════════════════════════════════════════════════════════════════
    // Main init — called for each [qa_map_and_timeline] shortcode instance
    // ═════════════════════════════════════════════════════════════════════════

    function initWidget($root) {
        if (!$root.length) return;

        $root.addClass('qamt-root');
        $root.attr('dir', 'rtl');

        // Override defaults from data attributes on the shortcode wrapper
        const yfAttr = parseInt($root.data('year-from'));
        const ytAttr = parseInt($root.data('year-to'));
        const evAttr = parseInt($root.data('event-id'));
        if (yfAttr) state.tlYearFrom = yfAttr;
        if (ytAttr) state.tlYearTo   = ytAttr;
        if (evAttr) state.filters.event_id = evAttr;

        // Structural layout
        $root.html(`
            <div class="qamt-layout">
                <section class="qamt-filters-section" id="qamt-filters"></section>
                <div class="qamt-views-layout">
                    <section class="qamt-map-section" id="qamt-map-section"></section>
                    <section class="qamt-timeline-section" id="qamt-tl-section"></section>
                </div>
            </div>
        `);

        // Build sub-components
        FilterBar.build($('#qamt-filters'));
        MapView.build($('#qamt-map-section'));
        TimelineView.build($('#qamt-tl-section'));

        // Load filter options then trigger first load
        apiGet('filter-options').then(res => {
            state.filterOptions = res;
            FilterBar.populate(res);
            // Apply any URL params
            parseUrlState();
            // Initial load
            MapView.load();
            TimelineView.load();
        }).fail(() => {
            MapView.load();
            TimelineView.load();
        });

        // Resize observer to fix Leaflet
        if (typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(() => {
                state.map && state.map.invalidateSize();
            });
            ro.observe(document.getElementById('qamt-map-section'));
        }

        // Tab switch on mobile
        initMobileTabs($root);
    }

    function initMobileTabs($root) {
        const $mapBtn = $root.find('[data-tab="map"]');
        const $tlBtn  = $root.find('[data-tab="timeline"]');
        // Mobile tab switching if tabs rendered by CSS
        $(document).on('click', '.qamt-tab-btn', function() {
            const tab = $(this).data('tab');
            $('.qamt-tab-btn').removeClass('active');
            $(this).addClass('active');
            if (tab === 'map') {
                $('#qamt-map-section').show();
                $('#qamt-tl-section').hide();
                state.map && setTimeout(() => state.map.invalidateSize(), 100);
            } else {
                $('#qamt-map-section').hide();
                $('#qamt-tl-section').show();
            }
        });
    }

    // URL state sync
    function parseUrlState() {
        const params = new URLSearchParams(window.location.search);
        params.forEach((v, k) => {
            if (v && ['evidence_type','verification_level','event_id','year','search'].includes(k)) {
                state.filters[k] = v;
            }
        });
        FilterBar.renderChips();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Document ready
    // ═════════════════════════════════════════════════════════════════════════

    $(document).ready(function() {
        // Initialize every [qa_map_and_timeline] block on the page
        $('.qamt-widget').each(function() {
            initWidget($(this));
        });
    });

})(jQuery);
