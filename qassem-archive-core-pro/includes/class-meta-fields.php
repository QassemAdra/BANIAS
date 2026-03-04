<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all custom meta fields via register_post_meta()
 * so they're available in REST API and block editor.
 */
class MetaFields {

    /**
     * Field schema definitions — single source of truth.
     * Used by register_post_meta() AND by MetaBoxes for rendering.
     *
     * @return array[]
     */
    public static function schema(): array {
        return [

            // ── Evidence fields ──────────────────────────────────────────
            'qa_evidence' => [

                // --- Core identification ---
                'qa_evidence_type' => [
                    'type'        => 'string',
                    'label'       => __( 'نوع الدليل', QA_TEXT_DOMAIN ),
                    'input'       => 'select',
                    'options'     => [
                        ''          => __( '— اختر —', QA_TEXT_DOMAIN ),
                        'video'     => __( 'فيديو', QA_TEXT_DOMAIN ),
                        'photo'     => __( 'صورة', QA_TEXT_DOMAIN ),
                        'document'  => __( 'وثيقة', QA_TEXT_DOMAIN ),
                        'testimony' => __( 'شهادة', QA_TEXT_DOMAIN ),
                    ],
                    'required'    => true,
                    'section'     => 'core',
                    'description' => '',
                ],

                'qa_event_date' => [
                    'type'        => 'string',
                    'label'       => __( 'تاريخ الحدث', QA_TEXT_DOMAIN ),
                    'input'       => 'date',
                    'required'    => false,
                    'section'     => 'core',
                    'description' => __( 'تاريخ وقوع الحدث (ليس تاريخ النشر)', QA_TEXT_DOMAIN ),
                ],

                'qa_event_date_precision' => [
                    'type'    => 'string',
                    'label'   => __( 'دقة التاريخ', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        'exact'   => __( 'دقيق', QA_TEXT_DOMAIN ),
                        'month'   => __( 'شهر فقط', QA_TEXT_DOMAIN ),
                        'year'    => __( 'سنة فقط', QA_TEXT_DOMAIN ),
                        'unknown' => __( 'غير معروف', QA_TEXT_DOMAIN ),
                    ],
                    'section' => 'core',
                ],

                // --- Relations ---
                'qa_location_id' => [
                    'type'        => 'integer',
                    'label'       => __( 'الموقع', QA_TEXT_DOMAIN ),
                    'input'       => 'post_select',
                    'post_type'   => 'qa_location',
                    'required'    => false,
                    'section'     => 'core',
                    'description' => '',
                ],

                'qa_event_id' => [
                    'type'        => 'integer',
                    'label'       => __( 'الحدث المرتبط', QA_TEXT_DOMAIN ),
                    'input'       => 'post_select',
                    'post_type'   => 'qa_event',
                    'required'    => false,
                    'section'     => 'core',
                    'description' => '',
                ],

                // --- Source ---
                'qa_source_type' => [
                    'type'    => 'string',
                    'label'   => __( 'نوع المصدر', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        ''             => __( '— اختر —', QA_TEXT_DOMAIN ),
                        'eyewitness'   => __( 'شاهد عيان', QA_TEXT_DOMAIN ),
                        'activist'     => __( 'ناشط', QA_TEXT_DOMAIN ),
                        'media'        => __( 'وسائل إعلام', QA_TEXT_DOMAIN ),
                        'organization' => __( 'منظمة', QA_TEXT_DOMAIN ),
                        'archive'      => __( 'أرشيف', QA_TEXT_DOMAIN ),
                    ],
                    'section' => 'core',
                ],

                'qa_source_url' => [
                    'type'        => 'string',
                    'label'       => __( 'رابط المصدر الأصلي', QA_TEXT_DOMAIN ),
                    'input'       => 'url',
                    'required'    => false,
                    'section'     => 'core',
                    'description' => __( 'اختياري — رابط المصدر الأصلي', QA_TEXT_DOMAIN ),
                ],

                // --- Verification ---
                'qa_verification_level' => [
                    'type'    => 'string',
                    'label'   => __( 'درجة التحقق', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        'unverified' => __( 'غير محقق', QA_TEXT_DOMAIN ),
                        'possible'   => __( 'محتمل', QA_TEXT_DOMAIN ),
                        'probable'   => __( 'غالب', QA_TEXT_DOMAIN ),
                        'verified'   => __( 'محقق', QA_TEXT_DOMAIN ),
                    ],
                    'default' => 'unverified',
                    'section' => 'verification',
                ],

                'qa_verification_notes' => [
                    'type'    => 'string',
                    'label'   => __( 'ملاحظات التحقق', QA_TEXT_DOMAIN ),
                    'input'   => 'textarea',
                    'rows'    => 4,
                    'section' => 'verification',
                ],

                'qa_verification_by' => [
                    'type'    => 'integer',
                    'label'   => __( 'تم التحقق بواسطة', QA_TEXT_DOMAIN ),
                    'input'   => 'readonly',
                    'section' => 'verification',
                ],

                'qa_verification_date' => [
                    'type'    => 'string',
                    'label'   => __( 'تاريخ التحقق', QA_TEXT_DOMAIN ),
                    'input'   => 'readonly',
                    'section' => 'verification',
                ],

                // --- Media ---
                'qa_media_storage' => [
                    'type'    => 'string',
                    'label'   => __( 'مصدر التخزين', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        'r2'       => __( 'Cloudflare R2', QA_TEXT_DOMAIN ),
                        'external' => __( 'رابط خارجي', QA_TEXT_DOMAIN ),
                        'wp'       => __( 'مكتبة WordPress', QA_TEXT_DOMAIN ),
                    ],
                    'default' => 'r2',
                    'section' => 'media',
                ],

                'qa_media_url' => [
                    'type'        => 'string',
                    'label'       => __( 'رابط الملف', QA_TEXT_DOMAIN ),
                    'input'       => 'url',
                    'required'    => false,
                    'section'     => 'media',
                    'description' => __( 'رابط R2 أو الرابط الخارجي للملف', QA_TEXT_DOMAIN ),
                ],

                'qa_thumb_url' => [
                    'type'        => 'string',
                    'label'       => __( 'رابط الصورة المصغرة', QA_TEXT_DOMAIN ),
                    'input'       => 'url',
                    'required'    => false,
                    'section'     => 'media',
                    'description' => __( 'اختياري', QA_TEXT_DOMAIN ),
                ],

                'qa_file_size' => [
                    'type'    => 'string',
                    'label'   => __( 'حجم الملف', QA_TEXT_DOMAIN ),
                    'input'   => 'text',
                    'section' => 'media',
                ],

                'qa_file_duration' => [
                    'type'        => 'string',
                    'label'       => __( 'مدة الفيديو/الصوت', QA_TEXT_DOMAIN ),
                    'input'       => 'text',
                    'section'     => 'media',
                    'description' => __( 'مثال: 00:02:35 (اختياري)', QA_TEXT_DOMAIN ),
                ],

                // --- Language ---
                'qa_language' => [
                    'type'    => 'string',
                    'label'   => __( 'لغة المحتوى', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        'ar' => __( 'عربي', QA_TEXT_DOMAIN ),
                        'en' => __( 'إنجليزي', QA_TEXT_DOMAIN ),
                        'fr' => __( 'فرنسي', QA_TEXT_DOMAIN ),
                    ],
                    'default' => 'ar',
                    'section' => 'core',
                ],

                // --- AI fields ---
                'qa_ai_status' => [
                    'type'    => 'string',
                    'label'   => __( 'حالة تحليل AI', QA_TEXT_DOMAIN ),
                    'input'   => 'readonly',
                    'options' => [
                        'idle'       => __( 'في الانتظار', QA_TEXT_DOMAIN ),
                        'processing' => __( 'جارٍ المعالجة', QA_TEXT_DOMAIN ),
                        'ready'      => __( 'مكتمل', QA_TEXT_DOMAIN ),
                        'error'      => __( 'خطأ', QA_TEXT_DOMAIN ),
                    ],
                    'default' => 'idle',
                    'section' => 'ai',
                ],

                'qa_ai_summary' => [
                    'type'    => 'string',
                    'label'   => __( 'ملخص AI', QA_TEXT_DOMAIN ),
                    'input'   => 'ai_textarea',
                    'rows'    => 5,
                    'section' => 'ai',
                ],

                'qa_ai_tags' => [
                    'type'        => 'string',
                    'label'       => __( 'كلمات مفتاحية (AI)', QA_TEXT_DOMAIN ),
                    'input'       => 'ai_json',
                    'section'     => 'ai',
                    'description' => __( 'JSON array من الكلمات المفتاحية', QA_TEXT_DOMAIN ),
                ],

                'qa_ai_transcript' => [
                    'type'    => 'string',
                    'label'   => __( 'نص تفريغ الصوت', QA_TEXT_DOMAIN ),
                    'input'   => 'ai_textarea',
                    'rows'    => 8,
                    'section' => 'ai',
                ],

                'qa_ai_ocr_text' => [
                    'type'    => 'string',
                    'label'   => __( 'نص OCR', QA_TEXT_DOMAIN ),
                    'input'   => 'ai_textarea',
                    'rows'    => 8,
                    'section' => 'ai',
                ],

                'qa_ai_entities' => [
                    'type'        => 'string',
                    'label'       => __( 'كيانات مستخرجة (AI)', QA_TEXT_DOMAIN ),
                    'input'       => 'ai_json',
                    'section'     => 'ai',
                    'description' => __( 'أماكن، تواريخ، مصطلحات — بدون أشخاص', QA_TEXT_DOMAIN ),
                ],

                'qa_ai_last_run' => [
                    'type'    => 'string',
                    'label'   => __( 'آخر تشغيل AI', QA_TEXT_DOMAIN ),
                    'input'   => 'readonly',
                    'section' => 'ai',
                ],

                'qa_ai_error_message' => [
                    'type'    => 'string',
                    'label'   => __( 'رسالة خطأ AI', QA_TEXT_DOMAIN ),
                    'input'   => 'readonly',
                    'section' => 'ai',
                ],
            ],

            // ── Event fields ─────────────────────────────────────────────
            'qa_event' => [

                'qa_start_date' => [
                    'type'    => 'string',
                    'label'   => __( 'تاريخ البداية', QA_TEXT_DOMAIN ),
                    'input'   => 'date',
                    'section' => 'core',
                ],

                'qa_end_date' => [
                    'type'    => 'string',
                    'label'   => __( 'تاريخ النهاية', QA_TEXT_DOMAIN ),
                    'input'   => 'date',
                    'section' => 'core',
                ],

                'qa_location_id' => [
                    'type'      => 'integer',
                    'label'     => __( 'الموقع الرئيسي', QA_TEXT_DOMAIN ),
                    'input'     => 'post_select',
                    'post_type' => 'qa_location',
                    'section'   => 'core',
                ],

                'qa_event_summary' => [
                    'type'    => 'string',
                    'label'   => __( 'ملخص الحدث', QA_TEXT_DOMAIN ),
                    'input'   => 'textarea',
                    'rows'    => 5,
                    'section' => 'core',
                ],

                'qa_timeline_json' => [
                    'type'        => 'string',
                    'label'       => __( 'بيانات الخط الزمني (JSON)', QA_TEXT_DOMAIN ),
                    'input'       => 'json_editor',
                    'section'     => 'advanced',
                    'description' => __( 'اختياري — بيانات مخصصة للخط الزمني', QA_TEXT_DOMAIN ),
                ],
            ],

            // ── Location fields ──────────────────────────────────────────
            'qa_location' => [

                'qa_parent_location_id' => [
                    'type'      => 'integer',
                    'label'     => __( 'الموقع الأم', QA_TEXT_DOMAIN ),
                    'input'     => 'post_select',
                    'post_type' => 'qa_location',
                    'section'   => 'core',
                ],

                'qa_lat' => [
                    'type'    => 'number',
                    'label'   => __( 'خط العرض (Latitude)', QA_TEXT_DOMAIN ),
                    'input'   => 'text',
                    'section' => 'geo',
                ],

                'qa_lng' => [
                    'type'    => 'number',
                    'label'   => __( 'خط الطول (Longitude)', QA_TEXT_DOMAIN ),
                    'input'   => 'text',
                    'section' => 'geo',
                ],

                'qa_geo_precision' => [
                    'type'    => 'string',
                    'label'   => __( 'دقة الموقع الجغرافي', QA_TEXT_DOMAIN ),
                    'input'   => 'select',
                    'options' => [
                        'city'         => __( 'مدينة', QA_TEXT_DOMAIN ),
                        'district'     => __( 'منطقة', QA_TEXT_DOMAIN ),
                        'neighborhood' => __( 'حي', QA_TEXT_DOMAIN ),
                        'exact'        => __( 'دقيق', QA_TEXT_DOMAIN ),
                        'unknown'      => __( 'غير معروف', QA_TEXT_DOMAIN ),
                    ],
                    'default' => 'city',
                    'section' => 'geo',
                ],

                'qa_map_bounds' => [
                    'type'        => 'string',
                    'label'       => __( 'حدود الخريطة (JSON)', QA_TEXT_DOMAIN ),
                    'input'       => 'text',
                    'section'     => 'geo',
                    'description' => __( 'مثال: [[34.5,35.5],[36.0,37.0]] (اختياري)', QA_TEXT_DOMAIN ),
                ],

                'qa_aliases' => [
                    'type'        => 'string',
                    'label'       => __( 'الأسماء البديلة', QA_TEXT_DOMAIN ),
                    'input'       => 'text',
                    'section'     => 'core',
                    'description' => __( 'أسماء بديلة مفصولة بفاصلة', QA_TEXT_DOMAIN ),
                ],
            ],
        ];
    }

    // ─── register_post_meta() for REST API access ─────────────────────────
    public function register(): void {
        add_action( 'init', [ $this, 'register_all_meta' ], 10 );
    }

    public function register_all_meta(): void {
        foreach ( self::schema() as $post_type => $fields ) {
            foreach ( $fields as $meta_key => $field ) {
                $args = [
                    'type'         => $field['type'] ?? 'string',
                    'single'       => true,
                    'show_in_rest' => true,
                    'default'      => $field['default'] ?? '',
                ];
                register_post_meta( $post_type, $meta_key, $args );
            }
        }
    }

    // ─── Helper: get field label ──────────────────────────────────────────
    public static function get_label( string $post_type, string $meta_key ): string {
        return self::schema()[ $post_type ][ $meta_key ]['label'] ?? $meta_key;
    }
}
