<?php
namespace QA\AI;

defined('ABSPATH') || exit;

class AIAudit {

    public static function log(int $evidence_id, ?int $user_id, string $action, $before = null, $after = null): void {
        global $wpdb;
        $table = $wpdb->prefix . 'qa_ai_audit';

        $wpdb->insert(
            $table,
            [
                'evidence_id' => $evidence_id,
                'user_id'     => $user_id ?: null,
                'action'      => $action,
                'data_before' => $before ? wp_json_encode($before, JSON_UNESCAPED_UNICODE) : null,
                'data_after'  => $after  ? wp_json_encode($after,  JSON_UNESCAPED_UNICODE) : null,
                'created_at'  => gmdate('Y-m-d H:i:s'),
            ],
            ['%d','%d','%s','%s','%s','%s']
        );
    }
}
