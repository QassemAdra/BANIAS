<?php
namespace QA\R2;

defined('ABSPATH') || exit;

/**
 * Cloudflare R2 Manager (S3-compatible)
 *
 * Provides REST endpoint to generate a presigned PUT URL so uploads
 * can go directly from browser -> R2.
 */
class Manager {

    public function register(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {
        register_rest_route( 'qassem/v1', '/r2/presign', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'presign' ],
            'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
            'args'                => [
                'filename' => [ 'required' => true, 'sanitize_callback' => 'sanitize_file_name' ],
                'content_type' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );
    }

    public function presign( \WP_REST_Request $req ): \WP_REST_Response {
        $settings = get_option( 'qa_settings', [] );

        $account  = trim( (string) ( $settings['r2_account_id'] ?? '' ) );
        $bucket   = trim( (string) ( $settings['r2_bucket_name'] ?? '' ) );
        $ak       = trim( (string) ( $settings['r2_access_key'] ?? '' ) );
        $sk       = trim( (string) ( $settings['r2_secret_key'] ?? '' ) );
        $public   = trim( (string) ( $settings['r2_public_url'] ?? '' ) );
        $region   = 'auto';
        $endpoint = $account ? ('https://' . $account . '.r2.cloudflarestorage.com') : '';

        if ( ! $endpoint || ! $bucket || ! $ak || ! $sk ) {
            return new \WP_REST_Response( [ 'message' => 'R2 settings are incomplete.' ], 400 );
        }

        $filename    = (string) $req->get_param('filename');
        $contentType = (string) $req->get_param('content_type');

        // Use date-based path to keep bucket tidy
        $key = 'evidence/' . gmdate('Y/m/') . wp_generate_password( 8, false, false ) . '-' . $filename;

        $signer = new Signer( $endpoint, $region, $ak, $sk );

        $signed = $signer->presign_put( $bucket, $key, $contentType, 900 );

        $publicUrl = $public ? rtrim( $public, '/' ) . '/' . ltrim( $key, '/' ) : '';

        return new \WP_REST_Response( [
            'uploadUrl'  => $signed,
            'objectKey'  => $key,
            'publicUrl'  => $publicUrl,
        ], 200 );
    }
}
