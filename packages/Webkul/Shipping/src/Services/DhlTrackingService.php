<?php

namespace Webkul\Shipping\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Shipment;
use Webkul\Shipping\Data\DhlCheckpointCodes;

class DhlTrackingService
{
    /**
     * @return array{success: bool, checkpoints?: list<array<string, mixed>>, latest?: array<string, mixed>|null, error?: string}
     */
    public function fetchTracking(string $trackingNumber): array
    {
        try {
            $apiKey = $this->dhlConfig('api_key');
            $apiSecret = $this->dhlConfig('api_secret');

            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('DHL API credentials are not configured');
            }

            $sandboxMode = $this->dhlConfig('sandbox_mode');
            $baseUrl = $sandboxMode
                ? 'https://express.api.dhl.com/mydhlapi/test'
                : 'https://express.api.dhl.com/mydhlapi';

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept'                 => 'application/json',
                    'Message-Reference'      => uniqid('', true),
                    'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
                ])
                ->withBasicAuth($apiKey, $apiSecret)
                ->get($baseUrl.'/shipments/'.urlencode($trackingNumber).'/tracking', [
                    'trackingView'    => 'all-checkpoints',
                    'levelOfDetail'   => 'all',
                ]);

            if (! $response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['detail'] ?? $errorBody['message'] ?? 'Unable to fetch DHL tracking';

                return [
                    'success' => false,
                    'error'   => $errorMessage,
                ];
            }

            $data = $response->json();
            $checkpoints = $this->normalizeCheckpoints($data);
            $latest = $checkpoints[0] ?? null;

            return [
                'success'     => true,
                'checkpoints' => $checkpoints,
                'latest'      => $latest,
            ];
        } catch (\Exception $e) {
            Log::error('DHL Tracking Error', [
                'tracking' => $trackingNumber,
                'message'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Refresh stored checkpoint on a shipment record.
     *
     * @return array{success: bool, shipment?: Shipment, error?: string}
     */
    public function refreshShipment(Shipment $shipment): array
    {
        if (empty($shipment->track_number)) {
            return [
                'success' => false,
                'error'   => 'No tracking number on this shipment.',
            ];
        }

        $result = $this->fetchTracking($shipment->track_number);

        if (! $result['success']) {
            return $result;
        }

        $latest = $result['latest'];
        $code = $latest['typeCode'] ?? null;

        $shipment->update([
            'dhl_last_checkpoint_code'        => $code,
            'dhl_last_checkpoint_description' => $latest['description'] ?? DhlCheckpointCodes::description($code),
            'dhl_tracking_fetched_at'         => now(),
        ]);

        return [
            'success'  => true,
            'shipment' => $shipment->fresh(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function normalizeCheckpoints(array $data): array
    {
        $events = [];

        if (! empty($data['shipments']) && is_array($data['shipments'])) {
            foreach ($data['shipments'] as $shipment) {
                if (! empty($shipment['events']) && is_array($shipment['events'])) {
                    $events = array_merge($events, $shipment['events']);
                }
            }
        }

        if (empty($events) && ! empty($data['events']) && is_array($data['events'])) {
            $events = $data['events'];
        }

        $normalized = [];

        foreach ($events as $event) {
            $code = $event['typeCode'] ?? $event['serviceArea']['code'] ?? null;
            $code = is_string($code) ? strtoupper(trim($code)) : null;

            $normalized[] = [
                'typeCode'    => $code,
                'description' => $event['description'] ?? DhlCheckpointCodes::description($code) ?? 'Update',
                'timestamp'   => $event['timestamp'] ?? $event['date'] ?? null,
                'location'    => $event['serviceArea']['description'] ?? $event['location']['address']['addressLocality'] ?? null,
            ];
        }

        usort($normalized, function ($a, $b) {
            return strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? ''));
        });

        return $normalized;
    }

    protected function dhlConfig(string $key, $default = null)
    {
        return core()->getConfigData('sales.carriers.dhl.'.$key) ?? $default;
    }
}
