<?php

namespace Webkul\Designer\Models;

use Illuminate\Database\Eloquent\Model;
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

    /**
     * Get the first image of the product.
     */
    public function featuredImage()
    {
        return $this->hasOne(DesignerImage::class)->orderBy('position')->oldest();
    }
}
