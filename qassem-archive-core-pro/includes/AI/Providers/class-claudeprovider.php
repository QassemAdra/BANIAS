<?php
namespace QA\AI\Providers;

defined('ABSPATH') || exit;

use QA\AI\AIProviderInterface;
use WP_Error;

class ClaudeProvider implements AIProviderInterface {

    private string $api_key;
    private string $model;

    public function __construct(string $api_key, string $model) {
        $this->api_key = $api_key;
        $this->model = $model ?: 'claude-3-5-sonnet-latest';
    }

    public function get_name(): string {
        return 'Anthropic Claude';
    }

    public function analyze(array $input): array {
        // Stub: not implemented in Phase 4 package.
        // You will implement the HTTP call here.
        return [
            'summary' => '',
            'tags' => [],
            'ocr_text' => '',
            'transcript' => '',
            'entities' => ['places'=>[], 'dates'=>[]],
            'raw' => ['provider'=>'claude','note'=>'stub'],
        ];
    }
}
