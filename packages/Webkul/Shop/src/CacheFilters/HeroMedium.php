<?php

namespace Webkul\Shop\CacheFilters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class HeroMedium implements FilterInterface
{
    /**
     * Apply filter.
     *
     * @return \Intervention\Image\Image
     */
    public function applyFilter(Image $image)
    {
        return $image->widen(1024, fn ($constraint) => $constraint->upsize());
    }
}
