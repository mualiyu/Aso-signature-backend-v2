<?php

namespace Webkul\Designer\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Designer\Contracts\DesignerImage as DesignerImageContract;

class DesignerImage extends Model implements DesignerImageContract
{
     protected $fillable = [
        'designer_id',
        'position',
        'src',
        'alt',
        'width',
        'height',
    ];

     /**
     * Get the product that owns the image.
     */
    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    // jigi jago

    /**
     * Get the URL for this image.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->src;
    }
}
