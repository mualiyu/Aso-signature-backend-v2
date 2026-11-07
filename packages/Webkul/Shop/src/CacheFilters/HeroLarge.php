<?php

namespace Webkul\Shop\CacheFilters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class HeroLarge implements FilterInterface
{
    /**
     * Apply filter.
     *
     * Hero slides are cropped by CSS to whatever the viewport needs, so these
     * templates only scale the image down and never change its aspect ratio.
     *
     * @return \Intervention\Image\Image
     */
    public function applyFilter(Image $image)
    {
        return $image->widen(1600, fn ($constraint) => $constraint->upsize());
    }
}
