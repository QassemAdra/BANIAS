<?php
namespace QA;

defined( 'ABSPATH' ) || exit;

/**
 * Seed demo data for testing.
 * Run via WP-CLI: wp eval "QA\Seeder::run();"
 * Or via admin: add ?qa_seed=1&qa_seed_nonce=... to any admin page (admin only)
 */
class Seeder {

    public static function run(): void {
        echo "🌱 جارٍ إنشاء البيانات التجريبية...\n";

        // Locations
        $banias_id    = self::create_location( 'بانياس', 35.1667, 35.9333, 'city' );
        $tartus_id    = self::create_location( 'طرطوس', 34.8919, 35.8866, 'city', 'Tartous' );
        $old_town_id  = self::create_location( 'البلد القديم — بانياس', 35.1620, 35.9280, 'neighborhood' );

        echo "✅ المواقع: بانياس ({$banias_id})، طرطوس ({$tartus_id})\n";

        // Events
        $event1_id = self::create_event(
            'أحداث بانياس 2011 — مرحلة الاحتجاجات الأولى',
            '2011-03-01',
            '2011-05-31',
            $banias_id,
            'المرحلة الأولى من الاحتجاجات الشعبية في بانياس، ابتداءً من مارس 2011.'
        );

        $event2_id = self::create_event(
            'مجزرة بانياس وبيضا — مايو 2013',
            '2013-05-02',
            '2013-05-04',
            $banias_id,
            'أحداث موثقة وقعت في منطقة بانياس وقرية البيضا في مايو 2013.'
        );

        echo "✅ الأحداث: حدث1 ({$event1_id})، حدث2 ({$event2_id})\n";

        // Evidence
        $e1 = self::create_evidence( [
            'title'              => 'فيديو — تظاهرة في بانياس مارس 2011',
            'content'            => 'تسجيل مصور يوثق تظاهرة سلمية في شوارع بانياس القديمة.',
            'evidence_type'      => 'video',
            'event_date'         => '2011-03-25',
            'location_id'        => $banias_id,
            'event_id'           => $event1_id,
            'source_type'        => 'activist',
            'verification_level' => 'probable',
            'verification_notes' => 'تم التحقق من الموقع الجغرافي عبر مطابقة المباني.',
            'media_storage'      => 'external',
            'media_url'          => 'https://example.com/sample-video.mp4',
        ] );

        $e2 = self::create_evidence( [
            'title'              => 'صورة — آثار قصف في بانياس 2013',
            'content'            => 'صورة توثق آثار الدمار في منطقة سكنية في بانياس.',
            'evidence_type'      => 'photo',
            'event_date'         => '2013-05-03',
            'location_id'        => $banias_id,
            'event_id'           => $event2_id,
            'source_type'        => 'media',
            'verification_level' => 'possible',
            'verification_notes' => '',
            'media_storage'      => 'external',
            'media_url'          => 'https://placehold.co/800x600/2c3e50/fff?text=Evidence+Photo',
        ] );

        $e3 = self::create_evidence( [
            'title'              => 'شهادة — شاهد عيان على أحداث البيضا',
            'content'            => 'شهادة شفهية مسجلة لشاهد عيان يروي ما رآه خلال أحداث مايو 2013.',
            'evidence_type'      => 'testimony',
            'event_date'         => '2013-05-10',
            'location_id'        => $banias_id,
            'event_id'           => $event2_id,
            'source_type'        => 'eyewitness',
            'verification_level' => 'unverified',
            'verification_notes' => 'بانتظار التحقق من هوية الشاهد.',
            'media_storage'      => 'external',
            'media_url'          => '',
        ] );

        $e4 = self::create_evidence( [
            'title'              => 'وثيقة — قائمة أسماء ضحايا موثقة',
            'content'            => 'وثيقة تحتوي على قائمة بأسماء ضحايا موثقة من منظمة حقوقية.',
            'evidence_type'      => 'document',
            'event_date'         => '2013-06-01',
            'location_id'        => $tartus_id,
            'event_id'           => $event2_id,
            'source_type'        => 'organization',
            'verification_level' => 'verified',
            'verification_notes' => 'تم التحقق من المنظمة المصدر وصحة الوثيقة.',
            'media_storage'      => 'external',
            'media_url'          => 'https://example.com/sample-doc.pdf',
        ] );

        echo "✅ الأدلة: ({$e1}، {$e2}، {$e3}، {$e4})\n";

        // Taxonomies
        $topic1 = self::create_term( 'احتجاجات', 'qa_topic' );
        $topic2 = self::create_term( 'انتهاكات حقوق الإنسان', 'qa_topic' );
        $topic3 = self::create_term( 'بانياس 2011', 'qa_topic', $topic1 );
        $tag1   = self::create_term( 'بانياس', 'qa_tag' );
        $tag2   = self::create_term( '2011', 'qa_tag' );
        $tag3   = self::create_term( '2013', 'qa_tag' );

        wp_set_post_terms( $e1, [ $topic3, $topic1 ], 'qa_topic' );
        wp_set_post_terms( $e1, [ $tag1, $tag2 ],     'qa_tag' );
        wp_set_post_terms( $e2, [ $topic2 ],           'qa_topic' );
        wp_set_post_terms( $e2, [ $tag1, $tag3 ],      'qa_tag' );

        echo "✅ تمت إضافة التصنيفات والوسوم.\n";
        echo "🎉 اكتملت البيانات التجريبية بنجاح!\n";
    }

    private static function create_location( string $title, float $lat, float $lng, string $precision, string $aliases = '' ): int {
        $id = wp_insert_post( [
            'post_type'   => 'qa_location',
            'post_title'  => $title,
            'post_status' => 'publish',
        ] );

        if ( is_wp_error( $id ) ) return 0;

        update_post_meta( $id, 'qa_lat',           $lat );
        update_post_meta( $id, 'qa_lng',           $lng );
        update_post_meta( $id, 'qa_geo_precision', $precision );
        if ( $aliases ) update_post_meta( $id, 'qa_aliases', $aliases );

        return $id;
    }

    private static function create_event( string $title, string $start, string $end, int $location_id, string $summary ): int {
        $id = wp_insert_post( [
            'post_type'    => 'qa_event',
            'post_title'   => $title,
            'post_content' => $summary,
            'post_status'  => 'publish',
        ] );

        if ( is_wp_error( $id ) ) return 0;

        update_post_meta( $id, 'qa_start_date',    $start );
        update_post_meta( $id, 'qa_end_date',      $end );
        update_post_meta( $id, 'qa_location_id',   $location_id );
        update_post_meta( $id, 'qa_event_summary', $summary );

        return $id;
    }

    private static function create_evidence( array $data ): int {
        $id = wp_insert_post( [
            'post_type'    => 'qa_evidence',
            'post_title'   => $data['title'],
            'post_content' => $data['content'] ?? '',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id() ?: 1,
        ] );

        if ( is_wp_error( $id ) ) return 0;

        $meta_keys = [
            'evidence_type', 'event_date', 'location_id', 'event_id',
            'source_type', 'source_url', 'verification_level', 'verification_notes',
            'media_storage', 'media_url', 'thumb_url',
        ];

        foreach ( $meta_keys as $key ) {
            if ( isset( $data[ $key ] ) ) {
                update_post_meta( $id, 'qa_' . $key, $data[ $key ] );
            }
        }

        update_post_meta( $id, 'qa_ai_status', 'idle' );

        return $id;
    }

    private static function create_term( string $name, string $taxonomy, int $parent = 0 ): int {
        $existing = get_term_by( 'name', $name, $taxonomy );
        if ( $existing ) return $existing->term_id;

        $args = $parent ? [ 'parent' => $parent ] : [];
        $term = wp_insert_term( $name, $taxonomy, $args );
        return is_wp_error( $term ) ? 0 : $term['term_id'];
    }
}
