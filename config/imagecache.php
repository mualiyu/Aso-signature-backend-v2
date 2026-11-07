<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Name of route
    |--------------------------------------------------------------------------
    |
    | Enter the routes name to enable dynamic imagecache manipulation.
    | This handle will define the first part of the URI:
    |
    | {route}/{template}/{filename}
    |
    | Examples: "images", "img/cache"
    |
     */

    'route' => 'cache',

    /*
    |--------------------------------------------------------------------------
    | Storage paths
    |--------------------------------------------------------------------------
    |
    | The following paths will be searched for the image filename, submited
    | by URI.
    |
    | Define as many directories as you like.
    |
     */

    'paths' => [
        storage_path('app/public'),
        public_path('storage'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Manipulation templates
    |--------------------------------------------------------------------------
    |
    | Here you may specify your own manipulation filter templates.
    | The keys of this array will define which templates
    | are available in the URI:
    |
    | {route}/{template}/{filename}
    |
    | The values of this array will define which filter class
    | will be applied, by its fully qualified name.
    |
     */

    'templates' => [
        'small'  => 'Webkul\Shop\CacheFilters\Small',
        'medium' => 'Webkul\Shop\CacheFilters\Medium',
        'large'  => 'Webkul\Shop\CacheFilters\Large',

        /**
         * The templates above crop to a fixed banner ratio. Hero slides keep their own
         * aspect ratio and are only scaled down.
         */
        'hero-small'  => 'Webkul\Shop\CacheFilters\HeroSmall',
        'hero-medium' => 'Webkul\Shop\CacheFilters\HeroMedium',
        'hero-large'  => 'Webkul\Shop\CacheFilters\HeroLarge',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Cache Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime in minutes of the images handled by the imagecache route.
    |
     */

    'lifetime' => 525600,
];
