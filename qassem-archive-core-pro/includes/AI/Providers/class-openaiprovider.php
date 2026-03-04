<?php
namespace QA\AI\Providers;

defined('ABSPATH') || exit;

use QA\AI\AIProviderInterface;

class OpenAIProvider implements AIProviderInterface {

    private string $api_key;
    private string $model;

    public function __construct(string $api_key, string $model) {
        $this->api_key = $api_key;
        $this->model = $model ?: 'gpt-4.1-mini';
    }

    public function get_name(): string {
        return 'OpenAI';
    }

    public function analyze(array $input): array {
        // Stub: implement HTTP call later.
        return [
            'summary' => '',
            'tags' => [],
            'ocr_text' => '',
            'transcript' => '',
            'entities' => ['places'=>[], 'dates'=>[]],
            'raw' => ['provider'=>'openai','note'=>'stub'],
        ];
    }
}
