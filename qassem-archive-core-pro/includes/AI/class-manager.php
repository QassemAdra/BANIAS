<?php
namespace QA\AI;

use QA\AI\AISafety;
use QA\AI\AIAudit;
use QA\AI\Providers\DummyProvider;
use QA\AI\Providers\OpenAIProvider;
use QA\AI\Providers\ClaudeProvider;
use WP_Error;

defined('ABSPATH') || exit;

/**
 * AI Manager
 *
 * - Hooks into qa_run_ai_analysis to queue analysis
 * - Provides cron handler to process jobs
 * - Saves results into qa_ai_* post meta
 *
 * Safety: No identity, no face recognition, no accusations.
 */
class Manager {

    const CRON_HOOK = 'qa_ai_process_job';

    public function register(): void {
        add_action( 'qa_run_ai_analysis', [ $this, 'queue_job' ], 10, 1 );
        add_action( self::CRON_HOOK, [ $this, 'process_job' ], 10, 2 );
    }

    public function queue_job( int $evidence_id ): void {
        // Schedule a single cron event (run soon)
        if ( ! wp_next_scheduled( self::CRON_HOOK, [ $evidence_id, get_current_user_id() ] ) ) {
            wp_schedule_single_event( time() + 5, self::CRON_HOOK, [ $evidence_id, get_current_user_id() ] );
        }
    }

    public function process_job( int $evidence_id, int $user_id ): void {
        update_post_meta( $evidence_id, 'qa_ai_status', 'processing' );
        $post = get_post( $evidence_id );
        if ( ! $post || $post->post_type !== 'qa_evidence' ) {
            return;
        }

        $settings = get_option( 'qa_settings', [] );
        $provider = $settings['ai_provider'] ?? 'dummy';
        if ( empty($provider) ) { $provider = 'dummy'; }

	    // Basic provider configuration check (avoid fatal errors, return friendly message)
	    $provider_lc = strtolower( (string) $provider );
	    if ( $provider_lc !== 'dummy' ) {
	        $api_key = trim( (string) ( $settings['ai_api_key'] ?? '' ) );
	        if ( $api_key === '' ) {
	            update_post_meta( $evidence_id, 'qa_ai_status', 'error' );
	            update_post_meta( $evidence_id, 'qa_ai_error_message', 'AI provider key is missing. Configure AI settings or switch to Dummy.' );
	            AIAudit::log( $evidence_id, $user_id, 'ai_error', null, [ 'error' => 'missing_api_key', 'provider' => $provider_lc ] );
	            return;
	        }
	    }

        $payload = $this->build_payload( $evidence_id, $post, $settings );
        $result  = $this->call_provider( $provider, $payload, $settings );

        if ( is_wp_error( $result ) ) {
            update_post_meta( $evidence_id, 'qa_ai_status', 'error' );
            update_post_meta( $evidence_id, 'qa_ai_error_message', $result->get_error_message() );
            return;
        }

        $result = AISafety::sanitize_output( is_array($result) ? $result : [] );

        // Expected normalized response
        $summary   = sanitize_text_field( $result['summary'] ?? '' );
        $tags      = $result['tags'] ?? [];
        $entities  = $result['entities'] ?? [];
        $ocr       = $result['ocr_text'] ?? '';
        $transcript= $result['transcript'] ?? '';

        update_post_meta( $evidence_id, 'qa_ai_summary', $summary );
        update_post_meta( $evidence_id, 'qa_ai_tags', wp_json_encode( $tags, JSON_UNESCAPED_UNICODE ) );
        update_post_meta( $evidence_id, 'qa_ai_entities', wp_json_encode( $entities, JSON_UNESCAPED_UNICODE ) );

        if ( $ocr ) {
            update_post_meta( $evidence_id, 'qa_ai_ocr_text', wp_kses_post( $ocr ) );
        }
        if ( $transcript ) {
            update_post_meta( $evidence_id, 'qa_ai_transcript', wp_kses_post( $transcript ) );
        }

        update_post_meta( $evidence_id, 'qa_ai_status', 'ready' );
        AIAudit::log( $evidence_id, $user_id, 'ai_ready', null, [ 'summary' => $summary, 'tags' => $tags, 'entities' => $entities ] );
        delete_post_meta( $evidence_id, 'qa_ai_error_message' );
    }

    private function build_payload( int $id, \WP_Post $post, array $settings ): array {
        $type = get_post_meta( $id, 'qa_evidence_type', true );
        $date = get_post_meta( $id, 'qa_event_date', true );
        $loc  = (int) get_post_meta( $id, 'qa_location_id', true );
        $evt  = (int) get_post_meta( $id, 'qa_event_id', true );

        $loc_title = $loc ? get_the_title( $loc ) : '';
        $evt_title = $evt ? get_the_title( $evt ) : '';

        $media_url = get_post_meta( $id, 'qa_media_url', true );
        $desc      = wp_strip_all_tags( $post->post_content );

        $system = "أنت مساعد أرشفة أدلة تاريخية. مهمتك: تلخيص النص واستخراج كلمات مفتاحية وذكر أماكن/تواريخ مذكورة.\n"
                . "قيود صارمة: لا تتعرف على أشخاص/وجوه، لا تذكر أسماء أشخاص، لا اتهامات، لا استنتاجات قطعية.\n"
                . "المخرجات يجب أن تكون JSON فقط بالشكل: {summary:string, tags:[string], entities:{places:[string], dates:[string], topics:[string]}}";

        $user = [
            'evidence_type' => (string) $type,
            'event_date'    => (string) $date,
            'location'      => (string) $loc_title,
            'event'         => (string) $evt_title,
            'content'       => (string) $desc,
            'media_url'     => (string) $media_url,
            'language'      => 'ar',
        ];

        return [
            'system' => $system,
            'user'   => $user,
        ];
    }

    private function call_provider( string $provider, array $payload, array $settings ) {
        // Phase 4: Providers are pluggable. Default is DummyProvider (offline).
        $provider = strtolower( $provider );

        if ( $provider === 'dummy' ) {
            $p = new DummyProvider();
            return $p->analyze( $payload );
        }

        $api_key = (string) ( $settings['ai_api_key'] ?? '' );
        $model   = (string) ( $settings['ai_model'] ?? '' );

        if ( $provider === 'openai' ) {
            $p = new OpenAIProvider( $api_key, $model );
            return $p->analyze( $payload );
        }

        if ( $provider === 'claude' || $provider === 'anthropic' ) {
            $p = new ClaudeProvider( $api_key, $model );
            return $p->analyze( $payload );
        }

        return new WP_Error( 'qa_ai_provider', 'AI provider is not supported or not configured.' );
    }

    private function call_openai( array $payload, array $settings ) {
        $key   = trim( (string) ( $settings['ai_api_key'] ?? '' ) );
        $model = trim( (string) ( $settings['ai_model'] ?? 'gpt-4.1-mini' ) );
        $base  = rtrim( (string) ( $settings['ai_api_base'] ?? 'https://api.openai.com' ), '/' );

        if ( ! $key ) {
            return new \WP_Error( 'qa_ai', 'OpenAI API key is missing.' );
        }

        $body = [
            'model' => $model,
            'messages' => [
                [ 'role' => 'system', 'content' => $payload['system'] ],
                [ 'role' => 'user',   'content' => wp_json_encode( $payload['user'], JSON_UNESCAPED_UNICODE ) ],
            ],
            'temperature' => 0.2,
        ];

        $res = wp_remote_post( $base . '/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 45,
            'body'    => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $res ) ) return $res;
        $code = wp_remote_retrieve_response_code( $res );
        $raw  = wp_remote_retrieve_body( $res );
        if ( $code < 200 || $code >= 300 ) {
            return new \WP_Error( 'qa_ai', 'OpenAI error: ' . $code . ' ' . substr( $raw, 0, 500 ) );
        }

        $json = json_decode( $raw, true );
        $content = $json['choices'][0]['message']['content'] ?? '';
        return $this->normalize_json_content( $content );
    }

    private function call_claude( array $payload, array $settings ) {
        $key   = trim( (string) ( $settings['ai_api_key'] ?? '' ) );
        $model = trim( (string) ( $settings['ai_model'] ?? 'claude-3-5-sonnet-latest' ) );
        $base  = rtrim( (string) ( $settings['ai_api_base'] ?? 'https://api.anthropic.com' ), '/' );

        if ( ! $key ) {
            return new \WP_Error( 'qa_ai', 'Claude (Anthropic) API key is missing.' );
        }

        $body = [
            'model' => $model,
            'max_tokens' => 800,
            'temperature' => 0.2,
            'system' => $payload['system'],
            'messages' => [
                [ 'role' => 'user', 'content' => wp_json_encode( $payload['user'], JSON_UNESCAPED_UNICODE ) ],
            ],
        ];

        $res = wp_remote_post( $base . '/v1/messages', [
            'headers' => [
                'x-api-key'        => $key,
                'anthropic-version'=> '2023-06-01',
                'Content-Type'     => 'application/json',
            ],
            'timeout' => 45,
            'body'    => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $res ) ) return $res;
        $code = wp_remote_retrieve_response_code( $res );
        $raw  = wp_remote_retrieve_body( $res );
        if ( $code < 200 || $code >= 300 ) {
            return new \WP_Error( 'qa_ai', 'Claude error: ' . $code . ' ' . substr( $raw, 0, 500 ) );
        }

        $json = json_decode( $raw, true );
        // Anthropic returns an array of content blocks
        $blocks = $json['content'] ?? [];
        $text = '';
        if ( is_array( $blocks ) ) {
            foreach ( $blocks as $b ) {
                if ( ( $b['type'] ?? '' ) === 'text' ) $text .= (string) ( $b['text'] ?? '' );
            }
        }
        return $this->normalize_json_content( $text );
    }

    private function call_custom( array $payload, array $settings ) {
        $url = trim( (string) ( $settings['ai_custom_url'] ?? '' ) );
        if ( ! $url ) {
            return new \WP_Error( 'qa_ai', 'Custom AI URL is not configured.' );
        }
        $res = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'timeout' => 45,
            'body'    => wp_json_encode( $payload ),
        ] );

        if ( is_wp_error( $res ) ) return $res;
        $code = wp_remote_retrieve_response_code( $res );
        $raw  = wp_remote_retrieve_body( $res );
        if ( $code < 200 || $code >= 300 ) {
            return new \WP_Error( 'qa_ai', 'Custom AI error: ' . $code . ' ' . substr( $raw, 0, 500 ) );
        }
        $json = json_decode( $raw, true );
        if ( is_array( $json ) ) return $json;
        return $this->normalize_json_content( $raw );
    }

    private function normalize_json_content( string $content ) {
        $content = trim( $content );
        // Try to extract JSON substring if model wrapped it
        if ( $content && $content[0] !== '{' ) {
            $start = strpos( $content, '{' );
            $end   = strrpos( $content, '}' );
            if ( $start !== false && $end !== false && $end > $start ) {
                $content = substr( $content, $start, $end - $start + 1 );
            }
        }
        $json = json_decode( $content, true );
        if ( ! is_array( $json ) ) {
            return new \WP_Error( 'qa_ai', 'AI returned invalid JSON.' );
        }
        return [
            'summary'    => (string) ( $json['summary'] ?? '' ),
            'tags'       => is_array( $json['tags'] ?? null ) ? $json['tags'] : [],
            'entities'   => is_array( $json['entities'] ?? null ) ? $json['entities'] : [ 'places'=>[], 'dates'=>[], 'topics'=>[] ],
            'ocr_text'   => (string) ( $json['ocr_text'] ?? '' ),
            'transcript' => (string) ( $json['transcript'] ?? '' ),
        ];
    }
}
