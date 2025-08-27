<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Designer\Models\Designer;
use Webkul\Designer\Models\DesignerProxy;
use Webkul\Product\Contracts\ProductFlat as ProductFlatContract;

class ProductFlat extends Model implements ProductFlatContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_flat';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Ignorable attributes.
     *
     * @var array
     */
    protected $ignorableAttributes = [
        'pivot',
        'parent_id',
        'attribute_family_id',
    ];

    /**
     * Get the product that owns the attribute value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Get the product that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the product that owns the product.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(DesignerProxy::modelClass(), 'designer_id');
    }

    /**
     * Get the product variants that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Retrieve type instance.
     *
     * @return \Webkul\Product\Type\AbstractType
     */
    public function getTypeInstance()
    {
        return $this->product->getTypeInstance();
    }
}
