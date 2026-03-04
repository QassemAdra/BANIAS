<?php
namespace QA\Admin;

defined('ABSPATH') || exit;

use QA\AI\AIAudit;

class AIActions {

    public function register(): void {
        add_action('add_meta_boxes', [ $this, 'add_box' ]);
        add_action('admin_post_qa_ai_run', [ $this, 'handle_run' ]);
        add_action('admin_post_qa_ai_accept', [ $this, 'handle_accept' ]);
        add_action('admin_post_qa_ai_reject', [ $this, 'handle_reject' ]);
    }

    public function add_box(): void {
        add_meta_box(
            'qa_ai_actions_box',
            __('AI Assistant (Safe)', QA_TEXT_DOMAIN),
            [ $this, 'render_box' ],
            'qa_evidence',
            'side',
            'high'
        );
    }

    public function render_box(\WP_Post $post): void {
        $status = get_post_meta($post->ID, 'qa_ai_status', true) ?: 'idle';
        $summary = (string) get_post_meta($post->ID, 'qa_ai_summary', true);
        $tags_json = (string) get_post_meta($post->ID, 'qa_ai_tags', true);
        $tags = json_decode($tags_json, true);
        if (!is_array($tags)) $tags = [];

        wp_nonce_field('qa_ai_actions', 'qa_ai_nonce');

        echo '<p><strong>Status:</strong> ' . esc_html($status) . '</p>';
        echo '<p><a class="button button-primary" href="' . esc_url(
            wp_nonce_url(admin_url('admin-post.php?action=qa_ai_run&evidence_id='.$post->ID), 'qa_ai_actions', 'qa_ai_nonce')
        ) . '">'.esc_html__('Run AI Analysis', QA_TEXT_DOMAIN).'</a></p>';

        if ($summary || $tags) {
            echo '<hr>';
            if ($summary) {
                echo '<p><strong>'.esc_html__('Suggested summary', QA_TEXT_DOMAIN).'</strong></p>';
                echo '<div style="max-height:120px;overflow:auto;border:1px solid #ccd0d4;padding:8px;background:#fff;">' . esc_html($summary) . '</div>';
            }
            if ($tags) {
                echo '<p><strong>'.esc_html__('Suggested tags', QA_TEXT_DOMAIN).'</strong><br>' . esc_html(implode('، ', $tags)) . '</p>';
            }

            echo '<p style="display:flex;gap:8px;flex-wrap:wrap;">';
            echo '<a class="button" href="' . esc_url(
                wp_nonce_url(admin_url('admin-post.php?action=qa_ai_accept&evidence_id='.$post->ID), 'qa_ai_actions', 'qa_ai_nonce')
            ) . '">'.esc_html__('Accept', QA_TEXT_DOMAIN).'</a>';

            echo '<a class="button button-secondary" href="' . esc_url(
                wp_nonce_url(admin_url('admin-post.php?action=qa_ai_reject&evidence_id='.$post->ID), 'qa_ai_actions', 'qa_ai_nonce')
            ) . '">'.esc_html__('Reject', QA_TEXT_DOMAIN).'</a>';
            echo '</p>';

            echo '<p style="color:#646970;font-size:12px;">'.esc_html__('AI outputs are suggestions only. No identities, no accusations.', QA_TEXT_DOMAIN).'</p>';
        }
    }

    public function handle_run(): void {
        if (!current_user_can('edit_posts')) wp_die('Not allowed');
        check_admin_referer('qa_ai_actions', 'qa_ai_nonce');

        $id = (int) ($_GET['evidence_id'] ?? 0);
        if ($id) {
            update_post_meta($id, 'qa_ai_status', 'processing');
            AIAudit::log($id, get_current_user_id(), 'run', null, null);
            /**
             * Queue AI analysis
             */
            do_action('qa_run_ai_analysis', $id);
        }

        wp_safe_redirect(wp_get_referer() ?: admin_url('edit.php?post_type=qa_evidence'));
        exit;
    }

    public function handle_accept(): void {
        if (!current_user_can('edit_others_posts')) wp_die('Not allowed');
        check_admin_referer('qa_ai_actions', 'qa_ai_nonce');

        $id = (int) ($_GET['evidence_id'] ?? 0);
        if ($id) {
            $before = [
                'post_excerpt' => get_post_field('post_excerpt', $id),
                'tags' => wp_get_post_terms($id, 'qa_tag', ['fields'=>'names']),
            ];

            $summary = (string) get_post_meta($id, 'qa_ai_summary', true);
            $tags = json_decode((string) get_post_meta($id, 'qa_ai_tags', true), true);
            if (!is_array($tags)) $tags = [];

            if ($summary) {
                wp_update_post(['ID' => $id, 'post_excerpt' => $summary]);
            }
            if ($tags) {
                wp_set_object_terms($id, $tags, 'qa_tag', true);
            }

            $after = [
                'post_excerpt' => get_post_field('post_excerpt', $id),
                'tags' => wp_get_post_terms($id, 'qa_tag', ['fields'=>'names']),
            ];

            update_post_meta($id, 'qa_ai_last_review', 'accepted');
            AIAudit::log($id, get_current_user_id(), 'accept', $before, $after);
        }

        wp_safe_redirect(wp_get_referer() ?: admin_url('post.php?post='.$id.'&action=edit'));
        exit;
    }

    public function handle_reject(): void {
        if (!current_user_can('edit_others_posts')) wp_die('Not allowed');
        check_admin_referer('qa_ai_actions', 'qa_ai_nonce');

        $id = (int) ($_GET['evidence_id'] ?? 0);
        if ($id) {
            update_post_meta($id, 'qa_ai_last_review', 'rejected');
            AIAudit::log($id, get_current_user_id(), 'reject', null, null);
        }

        wp_safe_redirect(wp_get_referer() ?: admin_url('post.php?post='.$id.'&action=edit'));
        exit;
    }
}
