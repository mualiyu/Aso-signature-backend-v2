<?php

namespace Webkul\Alteration\Services;

use Illuminate\Support\Carbon;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Sales\Models\Order;

/**
 * Decides whether a customer may file an alteration claim on an order:
 * delivered, within the reporting window, has a confirmed-measurement snapshot,
 * no claim already open, and the order's one-off ASO-covered alteration not yet used.
 */
class ClaimEligibility
{
    public const NOT_DELIVERED = 'not-delivered';

    public const WINDOW_CLOSED = 'window-closed';

    public const OPEN_CLAIM = 'open-claim';

    public const ONE_OFF_USED = 'one-off-used';

    public const NO_MEASUREMENTS = 'no-measurements';

    /**
     * @return array{eligible: bool, reason: ?string, deadline: ?Carbon, claim: ?AlterationClaim}
     */
    public function check(Order $order): array
    {
        $deadline = $this->deadline($order);

        $result = fn (?string $reason, ?AlterationClaim $claim = null) => [
            'eligible' => $reason === null,
            'reason'   => $reason,
            'deadline' => $deadline,
            'claim'    => $claim,
        ];

        if ($order->status !== Order::STATUS_COMPLETED || ! $deadline) {
            return $result(self::NOT_DELIVERED);
        }

        $claims = AlterationClaim::query()
            ->where('order_id', $order->id)
            ->latest('id')
            ->get();

        if ($open = $claims->first(fn ($claim) => $claim->isOpen())) {
            return $result(self::OPEN_CLAIM, $open);
        }

        if ($covered = $claims->first(fn ($claim) => in_array($claim->status, AlterationClaim::ASO_COVERED_STATUSES, true))) {
            return $result(self::ONE_OFF_USED, $covered);
        }

        if (now()->greaterThan($deadline)) {
            return $result(self::WINDOW_CLOSED, $claims->first());
        }

        if (! $order->measurements()->exists()) {
            return $result(self::NO_MEASUREMENTS);
        }

        return $result(null, $claims->first());
    }

    /**
     * Last moment a claim may be filed, or null if the order has not been delivered.
     */
    public function deadline(Order $order): ?Carbon
    {
        if (! $order->delivered_at) {
            return null;
        }

        return $order->delivered_at->copy()->addDays((int) config('alteration.reporting_window_days'))->endOfDay();
    }
}
