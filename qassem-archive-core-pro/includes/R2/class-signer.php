<?php
namespace QA\R2;

defined('ABSPATH') || exit;

/**
 * Minimal AWS Signature v4 presigner (S3 compatible)
 * Works for Cloudflare R2.
 */
class Signer {

    private $endpoint;
    private $region;
    private $access_key;
    private $secret_key;

    public function __construct( string $endpoint, string $region, string $access_key, string $secret_key ) {
        $this->endpoint   = rtrim( $endpoint, '/' );
        $this->region     = $region ?: 'auto';
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
    }

    public function presign_put( string $bucket, string $key, string $content_type, int $expires = 900 ): string {
        $method = 'PUT';

        $host = parse_url( $this->endpoint, PHP_URL_HOST );
        $scheme = parse_url( $this->endpoint, PHP_URL_SCHEME ) ?: 'https';

        $amz_date = gmdate('Ymd\THis\Z');
        $date     = gmdate('Ymd');

        $canonical_uri = '/' . rawurlencode( $bucket ) . '/' . $this->uri_encode( $key );

        $credential_scope = $date . '/' . $this->region . '/s3/aws4_request';
        $algorithm = 'AWS4-HMAC-SHA256';

        $query = [
            'X-Amz-Algorithm'  => $algorithm,
            'X-Amz-Credential' => rawurlencode( $this->access_key . '/' . $credential_scope ),
            'X-Amz-Date'       => $amz_date,
            'X-Amz-Expires'    => (string) $expires,
            'X-Amz-SignedHeaders' => 'host',
        ];

        ksort( $query );
        $canonical_query = $this->build_query( $query );

        $canonical_headers = 'host:' . $host . "\n";
        $signed_headers = 'host';

        // For presigned URL, payload is UNSIGNED-PAYLOAD
        $payload_hash = 'UNSIGNED-PAYLOAD';

        $canonical_request =
            $method . "\n" .
            $canonical_uri . "\n" .
            $canonical_query . "\n" .
            $canonical_headers . "\n" .
            $signed_headers . "\n" .
            $payload_hash;

        $string_to_sign =
            $algorithm . "\n" .
            $amz_date . "\n" .
            $credential_scope . "\n" .
            hash( 'sha256', $canonical_request );

        $signing_key = $this->get_signature_key( $this->secret_key, $date, $this->region, 's3' );
        $signature = hash_hmac( 'sha256', $string_to_sign, $signing_key );

        $canonical_query .= '&X-Amz-Signature=' . $signature;

        return $scheme . '://' . $host . $canonical_uri . '?' . $canonical_query;
    }

    private function build_query( array $query ): string {
        $pairs = [];
        foreach ( $query as $k => $v ) {
            $pairs[] = $k . '=' . $v;
        }
        return implode( '&', $pairs );
    }

    private function uri_encode( string $key ): string {
        // Encode each segment but keep slashes
        $segments = explode( '/', $key );
        $segments = array_map( function( $s ) {
            return rawurlencode( $s );
        }, $segments );
        return implode( '/', $segments );
    }

    private function get_signature_key( string $key, string $dateStamp, string $regionName, string $serviceName ) {
        $kDate = hash_hmac( 'sha256', $dateStamp, 'AWS4' . $key, true );
        $kRegion = hash_hmac( 'sha256', $regionName, $kDate, true );
        $kService = hash_hmac( 'sha256', $serviceName, $kRegion, true );
        $kSigning = hash_hmac( 'sha256', 'aws4_request', $kService, true );
        return $kSigning;
    }
}
