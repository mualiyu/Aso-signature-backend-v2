<?php

return [
    /**
     * Days after delivery within which a customer may file an alteration claim. Matches the
     * reporting window in the Refund & Return Policy.
     */
    'reporting_window_days' => 7,

    /**
     * Minimum number of photos a customer must attach to a claim.
     */
    'min_photos' => 3,

    'max_photos' => 8,

    /**
     * Max photo size in kilobytes.
     */
    'max_photo_size' => 5120,

    /**
     * Difference between the confirmed measurement and the garment measurement above which the
     * claim review screen highlights the row. Advisory only: staff decide the fault.
     */
    'tolerance' => [
        'cm'     => 2,
        'inches' => 0.75,
    ],

    /**
     * Known city centres for the tailor locator, keyed by ISO country code then slugged city
     * name. Used to rank partners by distance when no partner is in the searched city itself.
     * Add a city here when partner sourcing expands to it.
     */
    'cities' => [
        'GB' => [
            'london'     => [51.5074, -0.1278],
            'bristol'    => [51.4545, -2.5879],
            'manchester' => [53.4808, -2.2426],
            'birmingham' => [52.4862, -1.8904],
            'leeds'      => [53.8008, -1.5491],
            'cardiff'    => [51.4816, -3.1791],
            'glasgow'    => [55.8642, -4.2518],
            'reading'    => [51.4543, -0.9781],
            'oxford'     => [51.7520, -1.2577],
            'leicester'  => [52.6369, -1.1398],
        ],
        'CA' => [
            'vancouver' => [49.2827, -123.1207],
            'toronto'   => [43.6532, -79.3832],
            'calgary'   => [51.0447, -114.0719],
            'montreal'  => [45.5019, -73.5674],
            'ottawa'    => [45.4215, -75.6972],
        ],
        'FR' => [
            'paris' => [48.8566, 2.3522],
            'lyon'  => [45.7640, 4.8357],
        ],
        'US' => [
            'new_york'    => [40.7128, -74.0060],
            'houston'     => [29.7604, -95.3698],
            'atlanta'     => [33.7490, -84.3880],
            'washington'  => [38.9072, -77.0369],
            'los_angeles' => [34.0522, -118.2437],
            'chicago'     => [41.8781, -87.6298],
        ],
        'NG' => [
            'lagos' => [6.5244, 3.3792],
            'abuja' => [9.0765, 7.3986],
        ],
        'IE' => [
            'dublin' => [53.3498, -6.2603],
        ],
        'DE' => [
            'berlin' => [52.5200, 13.4050],
        ],
    ],

    /**
     * Partner outreach pipeline (task list item 9).
     */
    'outreach_statuses' => [
        'not_contacted',
        'contacted_no_response',
        'positive',
        'negative',
        'canceled',
    ],

    'vetting_statuses' => [
        'pending',
        'vetted',
        'rejected',
    ],

    /**
     * Minimum quality bar before a partner may be marked vetted (task list item 6). Every item
     * must be ticked.
     */
    'vetting_checklist' => [
        'sample_work_reviewed',
        'turnaround_agreed',
        'prices_agreed',
        'payment_terms_accepted',
        'liability_terms_accepted',
        'contact_verified',
    ],

    'claim_reasons' => [
        'measurement_mismatch',
        'material_quality_mismatch',
        'workmanship_defect',
    ],

    'claim_statuses' => [
        'submitted',
        'under_review',
        'info_requested',
        'aso_fault_approved',
        'customer_fault',
        'rejected',
        'partner_assigned',
        'completed',
        'closed',
    ],

    /**
     * Claim statuses that still block a new claim on the same order.
     */
    'open_claim_statuses' => [
        'submitted',
        'under_review',
        'info_requested',
        'aso_fault_approved',
        'partner_assigned',
    ],
];
