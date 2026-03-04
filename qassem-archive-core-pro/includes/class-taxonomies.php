<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

class Taxonomies {

    public function register(): void {
        add_action( 'init', [ $this, 'register_topic' ], 6 );
        add_action( 'init', [ $this, 'register_tag'   ], 6 );
    }

    // ─── Topics (hierarchical like categories) ────────────────────────────
    public function register_topic(): void {
        $labels = [
            'name'              => __( 'المواضيع', QA_TEXT_DOMAIN ),
            'singular_name'     => __( 'موضوع', QA_TEXT_DOMAIN ),
            'search_items'      => __( 'بحث في المواضيع', QA_TEXT_DOMAIN ),
            'all_items'         => __( 'كل المواضيع', QA_TEXT_DOMAIN ),
            'parent_item'       => __( 'الموضوع الرئيسي', QA_TEXT_DOMAIN ),
            'parent_item_colon' => __( 'الموضوع الرئيسي:', QA_TEXT_DOMAIN ),
            'edit_item'         => __( 'تعديل الموضوع', QA_TEXT_DOMAIN ),
            'add_new_item'      => __( 'إضافة موضوع جديد', QA_TEXT_DOMAIN ),
            'not_found'         => __( 'لا توجد مواضيع', QA_TEXT_DOMAIN ),
        ];

        register_taxonomy( 'qa_topic', [ 'qa_evidence', 'qa_event' ], [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'topic' ],
            'show_in_nav_menus' => true,
        ] );
    }

    // ─── Tags (non-hierarchical) ──────────────────────────────────────────
    public function register_tag(): void {
        $labels = [
            'name'          => __( 'الوسوم', QA_TEXT_DOMAIN ),
            'singular_name' => __( 'وسم', QA_TEXT_DOMAIN ),
            'search_items'  => __( 'بحث في الوسوم', QA_TEXT_DOMAIN ),
            'all_items'     => __( 'كل الوسوم', QA_TEXT_DOMAIN ),
            'edit_item'     => __( 'تعديل الوسم', QA_TEXT_DOMAIN ),
            'add_new_item'  => __( 'إضافة وسم جديد', QA_TEXT_DOMAIN ),
            'not_found'     => __( 'لا توجد وسوم', QA_TEXT_DOMAIN ),
        ];

        register_taxonomy( 'qa_tag', [ 'qa_evidence' ], [
            'labels'            => $labels,
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'qa-tag' ],
        ] );
    }
}
