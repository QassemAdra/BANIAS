<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

class PostTypes {

    public function register(): void {
        add_action( 'init', [ $this, 'register_evidence'  ], 5 );
        add_action( 'init', [ $this, 'register_event'     ], 5 );
        add_action( 'init', [ $this, 'register_location'  ], 5 );
    }

    // ─── Evidence ─────────────────────────────────────────────────────────
    public function register_evidence(): void {
        $labels = [
            'name'               => __( 'الأدلة', QA_TEXT_DOMAIN ),
            'singular_name'      => __( 'دليل', QA_TEXT_DOMAIN ),
            'menu_name'          => __( 'الأدلة', QA_TEXT_DOMAIN ),
            'add_new'            => __( 'إضافة دليل', QA_TEXT_DOMAIN ),
            'add_new_item'       => __( 'إضافة دليل جديد', QA_TEXT_DOMAIN ),
            'edit_item'          => __( 'تعديل الدليل', QA_TEXT_DOMAIN ),
            'new_item'           => __( 'دليل جديد', QA_TEXT_DOMAIN ),
            'view_item'          => __( 'عرض الدليل', QA_TEXT_DOMAIN ),
            'search_items'       => __( 'بحث في الأدلة', QA_TEXT_DOMAIN ),
            'not_found'          => __( 'لا توجد أدلة', QA_TEXT_DOMAIN ),
            'not_found_in_trash' => __( 'لا توجد أدلة في المهملات', QA_TEXT_DOMAIN ),
        ];

        register_post_type( 'qa_evidence', [
            'labels'              => $labels,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'qassem-archive',
            'show_in_rest'        => true,
            'rest_base'           => 'qa-evidence',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions', 'author' ],
            'has_archive'         => 'evidence',
            'rewrite'             => [ 'slug' => 'evidence', 'with_front' => false ],
            'menu_icon'           => 'dashicons-media-document',
            'menu_position'       => 25,
            'taxonomies'          => [ 'qa_topic', 'qa_tag' ],
            'custom_fields'       => true,
        ] );
    }

    // ─── Event ────────────────────────────────────────────────────────────
    public function register_event(): void {
        $labels = [
            'name'               => __( 'الأحداث', QA_TEXT_DOMAIN ),
            'singular_name'      => __( 'حدث', QA_TEXT_DOMAIN ),
            'menu_name'          => __( 'الأحداث', QA_TEXT_DOMAIN ),
            'add_new'            => __( 'إضافة حدث', QA_TEXT_DOMAIN ),
            'add_new_item'       => __( 'إضافة حدث جديد', QA_TEXT_DOMAIN ),
            'edit_item'          => __( 'تعديل الحدث', QA_TEXT_DOMAIN ),
            'not_found'          => __( 'لا توجد أحداث', QA_TEXT_DOMAIN ),
        ];

        register_post_type( 'qa_event', [
            'labels'          => $labels,
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => 'qassem-archive',
            'show_in_rest'    => true,
            'rest_base'       => 'qa-events',
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'supports'        => [ 'title', 'editor', 'revisions', 'author' ],
            'has_archive'     => 'events',
            'rewrite'         => [ 'slug' => 'events', 'with_front' => false ],
            'menu_icon'       => 'dashicons-calendar-alt',
            'menu_position'   => 26,
            'taxonomies'      => [ 'qa_topic' ],
        ] );
    }

    // ─── Location ─────────────────────────────────────────────────────────
    public function register_location(): void {
        $labels = [
            'name'               => __( 'المواقع', QA_TEXT_DOMAIN ),
            'singular_name'      => __( 'موقع', QA_TEXT_DOMAIN ),
            'menu_name'          => __( 'المواقع', QA_TEXT_DOMAIN ),
            'add_new'            => __( 'إضافة موقع', QA_TEXT_DOMAIN ),
            'add_new_item'       => __( 'إضافة موقع جديد', QA_TEXT_DOMAIN ),
            'edit_item'          => __( 'تعديل الموقع', QA_TEXT_DOMAIN ),
            'not_found'          => __( 'لا توجد مواقع', QA_TEXT_DOMAIN ),
        ];

        register_post_type( 'qa_location', [
            'labels'          => $labels,
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => 'qassem-archive',
            'show_in_rest'    => true,
            'rest_base'       => 'qa-locations',
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'hierarchical'    => true,
            'supports'        => [ 'title', 'editor', 'page-attributes' ],
            'has_archive'     => false,
            'rewrite'         => [ 'slug' => 'location', 'with_front' => false ],
            'menu_icon'       => 'dashicons-location',
            'menu_position'   => 27,
        ] );
    }
}
