<?php

return [
    [
        'key'    => 'sales.payment_methods.stripe',
        'name'   => 'Stripe',
        'sort'   => 6,
        'info'   => 'Accept card payments securely via Stripe Checkout.',
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
            ], [
                'name'          => 'logo',
                'title'         => 'Logo',
                'type'          => 'image',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'publishable_key',
                'title'         => 'Publishable Key',
                'type'          => 'text',
                'info'          => 'Stripe publishable key (pk_test_... or pk_live_...). Used for future client-side integrations.',
                'validation'    => 'required_if:sales.payment_methods.stripe.active,1',
                'channel_based' => false,
                'locale_based'  => false,
            ], [
                'name'          => 'secret_key',
                'title'         => 'Secret Key',
                'type'          => 'text',
                'info'          => 'Stripe secret key (sk_test_... or sk_live_...). Keep this confidential.',
                'validation'    => 'required_if:sales.payment_methods.stripe.active,1',
                'channel_based' => false,
                'locale_based'  => false,
            ], [
                'name'          => 'active',
                'title'         => 'Status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'            => 'sort',
                'title'           => 'Sort Order',
                'type'            => 'select',
                'options'         => [
                    ['title' => '1', 'value' => 1],
                    ['title' => '2', 'value' => 2],
                    ['title' => '3', 'value' => 3],
                    ['title' => '4', 'value' => 4],
                    ['title' => '5', 'value' => 5],
                    ['title' => '6', 'value' => 6],
                ],
                'default'         => 6,
                'title_attribute' => 'Sort Order',
                'validation'      => 'required|numeric',
                'channel_based'   => false,
                'locale_based'    => true,
            ],
        ],
    ],
];
