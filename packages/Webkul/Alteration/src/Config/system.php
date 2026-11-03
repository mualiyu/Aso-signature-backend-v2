<?php

return [
    [
        'key'  => 'sales.alteration',
        'name' => 'alteration::app.admin.configuration.title',
        'info' => 'alteration::app.admin.configuration.info',
        'icon' => 'settings/order.svg',
        'sort' => 8,
    ], [
        'key'    => 'sales.alteration.settings',
        'name'   => 'alteration::app.admin.configuration.settings.title',
        'info'   => 'alteration::app.admin.configuration.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'enabled',
                'title'   => 'alteration::app.admin.configuration.settings.enabled',
                'info'    => 'alteration::app.admin.configuration.settings.enabled-info',
                'type'    => 'boolean',
                'default' => false,
            ],
        ],
    ],
];
