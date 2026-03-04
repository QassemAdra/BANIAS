/**
 * Qassem Archive — Admin JS
 * Handles: Tab switching, AI run button, Accept/Dismiss AI fields, Location map preview
 */
(function ($) {
    'use strict';

    const qa = window.qaAdmin || {};

    /* ── Tab System ─────────────────────────────────────────────────── */
    function initTabs() {
        $(document).on('click', '.qa-tab-btn', function () {
            const $btn   = $(this);
            const tab    = $btn.data('tab');
            const $wrap  = $btn.closest('.qa-metabox-tabs');

            $wrap.find('.qa-tab-btn').removeClass('active');
            $wrap.find('.qa-tab-panel').removeClass('active');

            $btn.addClass('active');
            $wrap.find('[data-panel="' + tab + '"]').addClass('active');
        });
    }

    /* ── Run AI Analysis ─────────────────────────────────────────────── */
    function initAIButton() {
        $('#qa-run-ai-btn').on('click', function () {
            const $btn   = $(this);
            const postId = $btn.data('post-id');

            if (!postId) return;

            if (!confirm(qa.i18n.confirmRun)) return;

            $btn.prop('disabled', true)
                .html('<span class="qa-spinner"></span>' + qa.i18n.processing);

            $.ajax({
                url:    qa.restUrl + 'evidence/' + postId + '/run-ai',
                method: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', qa.restNonce);
                },
                success: function (res) {
                    $btn.html(qa.i18n.aiReady);
                    updateAIStatus('processing');
                    // Poll for completion
                    pollAIStatus(postId, $btn);
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || qa.i18n.aiError;
                    $btn.prop('disabled', false).html('🤖 ' + qa.i18n.runAI);
                    showAIError(msg);
                }
            });
        });
    }

    function pollAIStatus(postId, $btn) {
        let attempts = 0;
        const maxAttempts = 60; // 5 minutes max

        const interval = setInterval(function () {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(interval);
                $btn.prop('disabled', false).html('🤖 ' + qa.i18n.runAI);
                return;
            }

            $.ajax({
                url:    qa.restUrl + 'evidence/' + postId + '/ai-status',
                method: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', qa.restNonce);
                },
                success: function (res) {
                    const status = res.data?.ai_status;
                    updateAIStatus(status);

                    if (status === 'ready') {
                        clearInterval(interval);
                        $btn.prop('disabled', false).html('🤖 ' + qa.i18n.runAI);
                        // Reload page to show new AI data
                        setTimeout(() => location.reload(), 800);
                    } else if (status === 'error') {
                        clearInterval(interval);
                        $btn.prop('disabled', false).html('🤖 ' + qa.i18n.runAI);
                        showAIError(res.data?.error_message || qa.i18n.aiError);
                    }
                }
            });
        }, 5000); // Poll every 5 seconds
    }

    function updateAIStatus(status) {
        const classMap = {
            'idle':       'qa-status-idle',
            'processing': 'qa-status-processing',
            'ready':      'qa-status-ready',
            'error':      'qa-status-error',
        };
        const labelMap = {
            'idle':       'في الانتظار',
            'processing': 'جارٍ التحليل...',
            'ready':      'مكتمل',
            'error':      'خطأ',
        };
        const $status = $('.qa-ai-status');
        $status
            .attr('class', 'qa-ai-status ' + (classMap[status] || 'qa-status-idle'))
            .text(labelMap[status] || status);
    }

    function showAIError(msg) {
        const $notice = $('<div class="notice notice-error inline is-dismissible"><p></p></div>');
        $notice.find('p').text(msg);
        $('.qa-ai-header').after($notice);
    }

    /* ── AI Field Accept / Dismiss ───────────────────────────────────── */
    function initAIFieldActions() {
        // Accept: mark field as accepted (add visual indicator)
        $(document).on('click', '.qa-accept-ai', function () {
            const $btn   = $(this);
            const field  = $btn.data('field');
            const $field = $('#' + field);

            $btn.closest('.qa-ai-textarea-field, .qa-ai-json-field')
                .addClass('qa-field-accepted')
                .find('.qa-ai-field-actions')
                .html('<span style="color:#155724;font-weight:600;">✓ ' + qa.i18n.accepted + '</span>');

            // Log acceptance via AJAX
            $.post(qa.ajaxUrl, {
                action:  'qa_log_ai_acceptance',
                nonce:   qa.nonce,
                post_id: qa.postId,
                field:   field,
                value:   $field.val(),
            });
        });

        // Dismiss: clear the field
        $(document).on('click', '.qa-dismiss-ai', function () {
            const $btn  = $(this);
            const field = $btn.data('field');
            $('#' + field).val('');

            $btn.closest('.qa-ai-textarea-field, .qa-ai-json-field')
                .find('.qa-ai-tags-preview').remove().end()
                .find('.qa-ai-field-actions')
                .html('<span style="color:#721c24;font-size:12px;">' + qa.i18n.dismissed + '</span>');
        });
    }

    /* ── Settings Test Buttons ───────────────────────────────────────── */
    function initSettingsTests() {
        function testConnection(action, $resultEl) {
            $resultEl.removeClass('success error').text('⏳ جارٍ الاختبار...');
            $.ajax({
                url:    qa.ajaxUrl,
                method: 'POST',
                data:   { action, nonce: qa.nonce },
                success: function (res) {
                    if (res.success) {
                        $resultEl.addClass('success').text('✓ ' + res.data.message);
                    } else {
                        $resultEl.addClass('error').text('✗ ' + (res.data || 'فشل الاتصال'));
                    }
                },
                error: function () {
                    $resultEl.addClass('error').text('✗ خطأ في الاتصال');
                }
            });
        }

        const $result = $('#qa-test-result');
        $('#qa-test-r2').on('click', () => testConnection('qa_test_r2', $result));
        $('#qa-test-ai').on('click', () => testConnection('qa_test_ai', $result));

        $('#qa-rebuild-index').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('⏳ جارٍ إعادة البناء...');
            $.ajax({
                url:    qa.ajaxUrl,
                method: 'POST',
                data:   { action: 'qa_rebuild_index', nonce: qa.nonce },
                success: function(res) {
                    if (res.success) {
                        $('#qa-test-result').addClass('success').text('✓ ' + res.data.message);
                    } else {
                        $('#qa-test-result').addClass('error').text('✗ فشل');
                    }
                },
                error: function() {
                    $('#qa-test-result').addClass('error').text('✗ خطأ في الاتصال');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🔄 إعادة بناء فهرس البحث');
                }
            });
        });
    }

    /* ── Location Map Preview ────────────────────────────────────────── */
    function initLocationMap() {
        const $mapEl = $('#qa-location-map-preview');
        if (!$mapEl.length || typeof L === 'undefined') return;

        const defaultLat = parseFloat($('#qa_lat').val()) || 35.1667;
        const defaultLng = parseFloat($('#qa_lng').val()) || 35.9333;

        const map    = L.map('qa-location-map-preview').setView([defaultLat, defaultLng], 12);
        let   marker = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        function updateMarker(lat, lng) {
            if (marker) map.removeLayer(marker);
            if (lat && lng) {
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 14);
            }
        }

        // Init marker from current values
        updateMarker(defaultLat, defaultLng);

        // Update on input change
        $('#qa_lat, #qa_lng').on('change input', function () {
            const lat = parseFloat($('#qa_lat').val());
            const lng = parseFloat($('#qa_lng').val());
            if (!isNaN(lat) && !isNaN(lng)) updateMarker(lat, lng);
        });

        // Click on map to set coordinates
        map.on('click', function (e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            $('#qa_lat').val(lat);
            $('#qa_lng').val(lng);
            updateMarker(lat, lng);
        });
    }

    /* ── JSON Validator ──────────────────────────────────────────────── */
    function initJsonValidation() {
        $(document).on('blur', '.qa-json-editor', function () {
            const $el  = $(this);
            const val  = $el.val().trim();
            if (!val) return;
            try {
                JSON.parse(val);
                $el.css('border-color', '#28a745');
            } catch (e) {
                $el.css('border-color', '#dc3545');
                $el.after('<small class="qa-json-error" style="color:#dc3545;">JSON غير صحيح</small>');
                setTimeout(() => $el.next('.qa-json-error').remove(), 3000);
            }
        });
    }

    /* ── Init ────────────────────────────────────────────────────────── */
    
/* ── R2 Direct Upload (Presigned PUT) ───────────────────────────── */
function initR2Upload() {
    $(document).on('click', '.qa-r2-upload-btn', async function () {
        const $btn = $(this);
        const $wrap = $btn.closest('.qa-r2-upload');
        const fileInput = $wrap.find('.qa-r2-file').get(0);
        const $status = $wrap.find('.qa-r2-upload-status');
        const targetSel = $btn.data('target');
        const $target = $(targetSel);

        if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            alert(qa.i18n.selectFile);
            return;
        }
        const file = fileInput.files[0];

        $btn.prop('disabled', true);
        $status.text(qa.i18n.requestingUpload);

        try {
            const presignRes = await fetch(qa.restUrl + 'r2/presign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': qa.restNonce,
                },
                body: JSON.stringify({
                    filename: file.name,
                    content_type: file.type || 'application/octet-stream',
                })
            });

            const presignJson = await presignRes.json();
            if (!presignRes.ok) {
                throw new Error(presignJson?.message || 'Presign failed');
            }

            $status.text(qa.i18n.uploading);

            const uploadRes = await fetch(presignJson.uploadUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': file.type || 'application/octet-stream',
                },
                body: file
            });

            if (!uploadRes.ok) {
                throw new Error('Upload failed: ' + uploadRes.status);
            }

            const url = presignJson.publicUrl || '';
            if (url) {
                $target.val(url);
            } else {
                // if no public URL configured, store objectKey for later
                $target.val(presignJson.objectKey);
            }

            $status.text(qa.i18n.uploadDone);
        } catch (e) {
            console.error(e);
            $status.text(qa.i18n.uploadError + ': ' + e.message);
            alert(qa.i18n.uploadError + ': ' + e.message);
        } finally {
            $btn.prop('disabled', false);
        }
    });
}


$(function () {
        initTabs();
        initAIButton();
        initR2Upload();
        initAIFieldActions();
        initSettingsTests();
        initLocationMap();
        initJsonValidation();
    });

})(jQuery);
