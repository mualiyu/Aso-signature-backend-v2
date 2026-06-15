<?php

namespace Webkul\Shipping\Data;

/**
 * DHL Express checkpoint codes (from DHL Tracking Events reference).
 */
class DhlCheckpointCodes
{
    /**
     * @var array<string, string>
     */
    public const CODES = [
        'AD' => 'Agreed delivery',
        'AF' => 'Arrived facility',
        'AR' => 'Arrival in delivery facility',
        'BA' => 'Bad address',
        'BN' => 'Customer broker notified',
        'BR' => 'Broker release',
        'CA' => 'Closed on arrival',
        'CC' => 'Awaiting consignee collection',
        'CD' => 'Controllable clearance delay',
        'CM' => 'Customer moved',
        'CR' => 'Clearance release',
        'CS' => 'Closed shipment',
        'DD' => 'Delivered damaged',
        'DF' => 'Depart facility',
        'DS' => 'Destroyed / disposal',
        'FD' => "Forward destination (DD's expected)",
        'HP' => 'Held for payment',
        'IC' => 'In clearance processing',
        'MC' => 'Miscode',
        'MD' => 'Missed delivery cycle',
        'MS' => 'Mis-sort',
        'ND' => 'Not delivered',
        'NH' => 'Not home',
        'OH' => 'On hold',
        'OK' => 'Delivery',
        'PD' => 'Partial delivery',
        'PL' => 'Processed at location',
        'PU' => 'Shipment pick up',
        'RD' => 'Refused delivery',
        'RR' => 'Response received',
        'RT' => 'Returned to consignor',
        'SA' => 'Shipment acceptance',
        'SC' => 'Service changed',
        'SS' => 'Shipment stopped',
        'TP' => "Forwarded to 3rd party - no DD's",
        'TR' => 'Record of transfer',
        'UD' => 'Uncontrollable clearance delay',
        'WC' => 'With delivering courier',
    ];

    public static function description(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        $code = strtoupper(trim($code));

        return self::CODES[$code] ?? null;
    }

    public static function isDelivered(?string $code): bool
    {
        return in_array(strtoupper(trim((string) $code)), ['OK', 'DD', 'PD'], true);
    }
}
