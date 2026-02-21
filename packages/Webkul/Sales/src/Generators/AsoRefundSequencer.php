<?php

namespace Webkul\Sales\Generators;

use Webkul\Sales\Contracts\Sequencer as SequencerContract;

class AsoRefundSequencer implements SequencerContract
{
    /**
     * Generate refund increment ID in format: REF-{Date&time-with-sec}-{random}
     * Example: REF-20251030223045-A7B2C9
     *
     * @return string
     */
    public function generate(): string
    {
        // Generate random 6-character alphanumeric string
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        return 'REF-' . now()->format('YmdHis') . '-' . $random;
    }
}

