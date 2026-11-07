<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Webkul\Alteration\Contracts\AlterationClaimImage as AlterationClaimImageContract;

class AlterationClaimImage extends Model implements AlterationClaimImageContract
{
    protected $table = 'alteration_claim_images';

    protected $fillable = [
        'alteration_claim_id',
        'path',
        'sort',
    ];

    protected $appends = ['url'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(AlterationClaimProxy::modelClass(), 'alteration_claim_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
