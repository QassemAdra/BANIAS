<?php
namespace QA\AI;

defined('ABSPATH') || exit;

interface AIProviderInterface {
    public function get_name(): string;

    /**
     * Analyze evidence and return ONLY safe suggestions.
     * Return shape:
     * [
     *  'summary' => string,
     *  'tags' => string[],
     *  'ocr_text' => string,
     *  'transcript' => string,
     *  'entities' => ['places'=>string[], 'dates'=>string[]],
     *  'raw' => mixed
     * ]
     */
    public function analyze(array $input): array;
}
