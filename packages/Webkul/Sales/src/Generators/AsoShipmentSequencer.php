<?php

namespace Webkul\Sales\Generators;

use Webkul\Sales\Contracts\Sequencer as SequencerContract;

class AsoShipmentSequencer implements SequencerContract
{
    /**
     * Generate shipment increment ID in format: SHP-{Date&time-with-sec}-{random}
     * Example: SHP-20251030223045-A7B2C9
     *
     * @return string
     */
    public function generate(): string
    {
        // Generate random 6-character alphanumeric string
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        return 'SHP-' . now()->format('YmdHis') . '-' . $random;
    }
}

