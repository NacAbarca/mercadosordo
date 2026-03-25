<?php
declare(strict_types=1);
namespace MercadoSordo\Core;

/**
 * Cloudflare R2 uploader — S3-compatible API
 * No requiere SDK, usa HTTP puro con firma AWS Signature V4
 */
class R2Uploader
{
    private string $bucket;
    private string $accessKey;
    private string $secretKey;
    private string $endpoint;
    private string $publicUrl;

    public function __construct()
    {
        $this->bucket    = getenv('R2_BUCKET')    ?: 'mercadosordo-uploads';
        $this->accessKey = getenv('R2_ACCESS_KEY') ?: '';
        $this->secretKey = getenv('R2_SECRET_KEY') ?: '';
        $this->endpoint  = getenv('R2_ENDPOINT')   ?: '';
        $this->publicUrl = getenv('R2_PUBLIC_URL') ?: '';
    }

    /**
     * Sube un archivo a R2 y retorna la URL pública
     */
    public function upload(string $tmpPath, string $filename, string $mimeType): string
    {
        $fileContent = file_get_contents($tmpPath);
        $fileSize    = strlen($fileContent);
        $date        = gmdate('Ymd');
        $datetime    = gmdate('Ymd\THis\Z');
        $region      = 'auto';
        $service     = 's3';

        // Parse host from endpoint
        $host = parse_url($this->endpoint, PHP_URL_HOST);
        $path = '/' . $this->bucket . '/' . $filename;

        // Headers canonicos
        $contentHash = hash('sha256', $fileContent);
        $headers = [
            'content-length' => (string)$fileSize,
            'content-type'   => $mimeType,
            'host'           => $host,
            'x-amz-content-sha256' => $contentHash,
            'x-amz-date'    => $datetime,
        ];
        ksort($headers);

        $canonicalHeaders  = '';
        $signedHeadersList = [];
        foreach ($headers as $k => $v) {
            $canonicalHeaders   .= $k . ':' . $v . "\n";
            $signedHeadersList[] = $k;
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = implode("\n", [
            'PUT',
            $path,
            '', // query string vacío
            $canonicalHeaders,
            $signedHeaders,
            $contentHash,
        ]);

        $credentialScope = $date . '/' . $region . '/' . $service . '/aws4_request';
        $stringToSign    = implode("\n", [
            'AWS4-HMAC-SHA256',
            $datetime,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->getSigningKey($date, $region, $service);
        $signature  = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->accessKey . '/' . $credentialScope
            . ', SignedHeaders=' . $signedHeaders
            . ', Signature=' . $signature;

        $url = $this->endpoint . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $fileContent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: '          . $authorization,
                'Content-Length: '         . $fileSize,
                'Content-Type: '           . $mimeType,
                'Host: '                   . $host,
                'x-amz-content-sha256: '   . $contentHash,
                'x-amz-date: '             . $datetime,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException("R2 upload failed [{$httpCode}]: " . $response);
        }

        return rtrim($this->publicUrl, '/') . '/' . $filename;
    }

    /**
     * Elimina un archivo de R2
     */
    public function delete(string $filename): void
    {
        if (empty($filename)) return;
        $date     = gmdate('Ymd');
        $datetime = gmdate('Ymd\THis\Z');
        $region   = 'auto';
        $service  = 's3';
        $host     = parse_url($this->endpoint, PHP_URL_HOST);
        $path     = '/' . $this->bucket . '/' . $filename;

        $contentHash = hash('sha256', '');
        $headers = [
            'host'                 => $host,
            'x-amz-content-sha256' => $contentHash,
            'x-amz-date'           => $datetime,
        ];
        ksort($headers);

        $canonicalHeaders  = '';
        $signedHeadersList = [];
        foreach ($headers as $k => $v) {
            $canonicalHeaders   .= $k . ':' . $v . "\n";
            $signedHeadersList[] = $k;
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = implode("\n", [
            'DELETE', $path, '',
            $canonicalHeaders, $signedHeaders, $contentHash,
        ]);

        $credentialScope = $date . '/' . $region . '/' . $service . '/aws4_request';
        $stringToSign    = implode("\n", [
            'AWS4-HMAC-SHA256', $datetime, $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey    = $this->getSigningKey($date, $region, $service);
        $signature     = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->accessKey . '/' . $credentialScope
            . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

        $ch = curl_init($this->endpoint . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: '        . $authorization,
                'Host: '                 . $host,
                'x-amz-content-sha256: ' . $contentHash,
                'x-amz-date: '           . $datetime,
            ],
        ]);
        curl_exec($ch);
        unset($ch);
    }

    /**
     * Extrae el filename de una URL de R2
     */
    public static function filenameFromUrl(string $url): string
    {
        return basename(parse_url($url, PHP_URL_PATH) ?? '');
    }

    private function getSigningKey(string $date, string $region, string $service): string
    {
        $kDate    = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion  = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    public static function isEnabled(): bool
    {
        return !empty(getenv('R2_ACCESS_KEY'));
    }
}
