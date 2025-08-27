<?php

namespace Webkul\Designer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Designer\Contracts\Designer as DesignerContract;
use Webkul\Product\Models\Product;

class Designer extends Model implements DesignerContract
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'website',
        'instagram',
        'facebook',
        'twitter',
        'pinterest',
        'linkedin',
        'youtube',
        'image',

        'status',
        'password',

    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

     /**
     * Get all images for the designer.
     */
    public function images()
    {
        return $this->hasMany(DesignerImage::class)->orderBy('position');
    }

    public function logo()
    {
        return $this->hasOne(DesignerImage::class, 'designer_id', 'id')->where('alt', 'logo_path')->latest();
    }

    public function banner()
    {
        return $this->hasOne(DesignerImage::class, 'designer_id', 'id')->where('alt', 'banner_path')->latest();
    }

    /**
     * Get image url for the category image.
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        // if (! $this->logo()->src) {
        //     return;
        // }

        return Storage::url($this->logo()->src);
    }

    /**
     * Get banner url attribute.
     *
     * @return string
     */
    public function getBannerUrlAttribute()
    {
        // if (! $this->banner()->src) {
        //     return;
        // }

        return Storage::url($this->banner()->src);
    }

    /**
     * Get the first image of the product.
     */
    public function featuredImage()
    {
        return $this->hasOne(DesignerImage::class)->orderBy('position')->oldest();
    }
}
