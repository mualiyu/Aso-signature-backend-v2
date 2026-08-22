<?php

/**
 * The ordered list of measurements shown on the "How to Measure" page.
 *
 * Every `id` must have a matching icon component at
 * `shop::components.how-to-measure.icons.{id}` and a matching
 * lang block at `shop::app.home.how-to-measure.measurements.{id}`.
 */
return [
    [
        'id'       => 'neck',
        'letter'   => 'A',
        'keywords' => ['neck', 'collar', 'throat'],
    ], [
        'id'       => 'shoulder',
        'letter'   => 'B',
        'keywords' => ['shoulder', 'back', 'across'],
    ], [
        'id'       => 'chest',
        'letter'   => 'C',
        'keywords' => ['chest', 'bust', 'torso'],
    ], [
        'id'       => 'waist',
        'letter'   => 'D',
        'keywords' => ['waist', 'stomach', 'middle'],
    ], [
        'id'       => 'hip',
        'letter'   => 'E',
        'keywords' => ['hip', 'seat', 'bottom'],
    ], [
        'id'       => 'sleeve',
        'letter'   => 'F',
        'keywords' => ['sleeve', 'arm', 'wrist'],
    ], [
        'id'       => 'top-length',
        'letter'   => 'G',
        'keywords' => ['top', 'length', 'shirt', 'agbada', 'kaftan'],
    ], [
        'id'       => 'inseam',
        'letter'   => 'H',
        'keywords' => ['inseam', 'leg', 'trouser', 'pants'],
    ],
];
