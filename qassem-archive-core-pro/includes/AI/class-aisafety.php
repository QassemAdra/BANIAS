<?php
namespace QA\AI;

defined('ABSPATH') || exit;

class AISafety {

    private static array $ban_phrases = [
        'هو المسؤول', 'المسؤول عن', 'الفاعل', 'المرتكب', 'جرائم', 'قاتل', 'إرهابي',
        'اسمه', 'يدعى', 'هوية', 'رقم وطني', 'عنوانه', 'رقم هاتف'
    ];

    public static function sanitize_output(array $out): array {
        foreach (['summary','ocr_text','transcript'] as $k) {
            if (!empty($out[$k]) && is_string($out[$k])) {
                $out[$k] = self::strip_banned($out[$k]);
                $out[$k] = self::strip_possible_pii($out[$k]);
            }
        }

        $entities = $out['entities'] ?? [];
        $out['entities'] = [
            'places' => array_values(array_filter($entities['places'] ?? [], fn($v)=>is_string($v) && mb_strlen($v) <= 60)),
            'dates'  => array_values(array_filter($entities['dates']  ?? [], fn($v)=>is_string($v) && mb_strlen($v) <= 30)),
        ];

        $tags = $out['tags'] ?? [];
        if (!is_array($tags)) $tags = [];
        $tags = array_slice(array_values(array_unique(array_filter($tags, fn($t)=>is_string($t) && mb_strlen($t) <= 30))), 0, 20);
        $out['tags'] = $tags;

        // Ensure keys exist
        $out += [
            'summary' => '',
            'ocr_text' => '',
            'transcript' => '',
            'entities' => ['places'=>[], 'dates'=>[]],
            'tags' => [],
        ];

        return $out;
    }

    private static function strip_banned(string $text): string {
        foreach (self::$ban_phrases as $p) {
            $text = str_ireplace($p, '[محذوف]', $text);
        }
        return $text;
    }

    private static function strip_possible_pii(string $text): string {
        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[محذوف]', $text);
        $text = preg_replace('/\+?\d[\d\s\-]{7,}\d/', '[محذوف]', $text);
        return $text;
    }
}
