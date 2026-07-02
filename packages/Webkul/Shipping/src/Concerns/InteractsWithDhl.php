<?php

namespace Webkul\Shipping\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Shared MyDHL API plumbing: configuration access, sandbox/production base-URL resolution, a
 * pre-authenticated HTTP client and error-body parsing. Kept in one place so credentials,
 * endpoints and error handling stay consistent across the DHL services.
 */
trait InteractsWithDhl
{
    /**
     * Read a DHL carrier setting from the admin "core config" store
     * (Admin → Configure → Sales → Shipping Methods → DHL Express).
     */
    protected function dhlConfig(string $key, $default = null)
    {
        return core()->getConfigData('sales.carriers.dhl.'.$key) ?? $default;
    }

    /**
     * MyDHL base URL, switched between the sandbox and production hosts by the `sandbox_mode` flag.
     */
    protected function dhlBaseUrl(): string
    {
        return $this->dhlConfig('sandbox_mode')
            ? 'https://express.api.dhl.com/mydhlapi/test'
            : 'https://express.api.dhl.com/mydhlapi';
    }

    /**
     * A MyDHL HTTP client with Basic auth and the required message headers already applied.
     *
     * @throws \RuntimeException when API credentials are not configured.
     */
    protected function dhlRequest(int $timeout = 30): PendingRequest
    {
        $apiKey = $this->dhlConfig('api_key');
        $apiSecret = $this->dhlConfig('api_secret');

        if (empty($apiKey) || empty($apiSecret)) {
            throw new \RuntimeException('DHL API credentials are not configured');
        }

        return Http::timeout($timeout)
            ->connectTimeout(min($timeout, 60))
            ->withHeaders([
                'Content-Type'           => 'application/json',
                'Accept'                 => 'application/json',
                'Message-Reference'      => uniqid('', true),
                'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
            ])
            ->withBasicAuth($apiKey, $apiSecret);
    }

    /**
     * Extract a human-readable message from a MyDHL error response, preferring the most specific
     * field DHL provides (detail → reasons[].msg → message) and appending 422 additionalDetails.
     */
    protected function parseDhlError(Response $response): string
    {
        $body = $response->json();

        if (! is_array($body)) {
            return 'Unknown error';
        }

        $message = 'Unknown error';

        if (! empty($body['detail'])) {
            $message = $body['detail'];
        } elseif (! empty($body['reasons'][0])) {
            $message = $body['reasons'][0]['msg'] ?? $body['reasons'][0]['message'] ?? $message;
        } elseif (! empty($body['message'])) {
            $message = $body['message'];
        }

        if ($response->status() === 422 && isset($body['additionalDetails'])) {
            $details = is_array($body['additionalDetails'])
                ? implode(', ', $body['additionalDetails'])
                : $body['additionalDetails'];

            $message .= ' Details: '.$details;
        }

        return $message;
    }
}
