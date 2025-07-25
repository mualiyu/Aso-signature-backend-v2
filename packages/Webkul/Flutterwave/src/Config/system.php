<?php

return [
    [
        'key'    => 'sales.payment_methods.flutterwave',
        'name'   => 'Flutterwave',
        'sort'   => 5,
        'info'   => 'Flutterwave is your all-in-one toolkit for integrating payment solutions into your application.',
        'icon'   => 'icon paypal',
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'Title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'Description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            // logo
            [
                'name'          => 'logo',
                'title'         => 'Logo',
                'type'          => 'image',
                'channel_based' => false,
                'locale_based'  => true,
            ],

            [
                'name'          => 'public_key',
                'title'         => 'Public Key',
                'type'          => 'text',
                'validation'    => 'required_if:sales.payment_methods.flutterwave.active,1',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'secret_key',
                'title'         => 'Secret Key',
                'type'          => 'text',
                'validation'    => 'required_if:sales.payment_methods.flutterwave.active,1',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            // [
            //     'name'          => 'sandbox',
            //     'title'         => 'Sandbox Mode',
            //     'type'          => 'boolean',
            //     'validation'    => 'required',
            //     'channel_based' => false,
            //     'locale_based'  => true,
            // ],
            [
                'name'          => 'active',
                'title'         => 'Status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'sort',
                'title'         => 'Sort Order',
                'type'          => 'select',
                'options'       => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                ],
                'default'       => 1,
                'title_attribute' => 'Sort Order',
                'validation'    => 'required|numeric',
                'channel_based' => false,
                'locale_based'  => true,
            ]
        ]
    ]
];
