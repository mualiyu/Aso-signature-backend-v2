<?php

namespace Webkul\Sales\Generators;

use Webkul\Sales\Contracts\Sequencer as SequencerContract;

class AsoOrderSequencer implements SequencerContract
{
    /**
     * Generate order increment ID in format: ASO-{Date&time-with-sec}-{random}
     * Example: ASO-20251030223045-A7B2C9
     *
     * @return string
     */
    public function generate(): string
    {
        // Generate random 6-character alphanumeric string
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        return 'ASO-' . now()->format('YmdHis') . '-' . $random;
    }
}

