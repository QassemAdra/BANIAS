<?php
namespace QA\AI\Providers;

defined('ABSPATH') || exit;

use QA\AI\AIProviderInterface;

class DummyProvider implements AIProviderInterface {

    public function get_name(): string {
        return 'Dummy (Offline)';
    }

    public function analyze(array $input): array {
        $title = $input['title'] ?? '';
        $desc  = $input['description'] ?? '';

        $summary = trim($title . ' — ' . wp_trim_words(wp_strip_all_tags($desc), 35));

        $tags = [];
        foreach (['بانياس','طرطوس','الساحل','مظاهرة','اعتصام','اعتقال','قصف','حاجز','توثيق','وثيقة','فيديو','صورة'] as $kw) {
            if (mb_strpos($title.' '.$desc, $kw) !== false) $tags[] = $kw;
        }
        $tags = array_values(array_unique($tags));

        $entities = [
            'places' => array_values(array_unique(array_filter([
                mb_strpos($title.' '.$desc,'بانياس') !== false ? 'بانياس' : null,
                mb_strpos($title.' '.$desc,'طرطوس') !== false ? 'طرطوس' : null,
                mb_strpos($title.' '.$desc,'الساحل') !== false ? 'الساحل السوري' : null,
            ]))),
            'dates' => [],
        ];

        return [
            'summary' => $summary,
            'tags' => $tags,
            'ocr_text' => '',
            'transcript' => '',
            'entities' => $entities,
            'raw' => ['provider' => 'dummy'],
        ];
    }
}
