<?php

namespace Webkul\Sales\Generators;

use Webkul\Sales\Contracts\Sequencer as SequencerContract;

class AsoInvoiceSequencer implements SequencerContract
{
    /**
     * Generate invoice increment ID in format: INV-{Date&time-with-sec}-{random}
     * Example: INV-20251030223045-A7B2C9
     *
     * @return string
     */
    public function generate(): string
    {
        // Generate random 6-character alphanumeric string
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 3));

        return 'ASO-INV-' . now()->format('YmdHis') . '-' . $random;
    }
}

