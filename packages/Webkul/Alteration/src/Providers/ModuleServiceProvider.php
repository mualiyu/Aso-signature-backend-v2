<?php

namespace Webkul\Alteration\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        \Webkul\Alteration\Models\AlterationPartner::class,
        \Webkul\Alteration\Models\AlterationPartnerPrice::class,
        \Webkul\Alteration\Models\AlterationFixType::class,
        \Webkul\Alteration\Models\AlterationClaim::class,
        \Webkul\Alteration\Models\AlterationClaimItem::class,
        \Webkul\Alteration\Models\AlterationClaimImage::class,
    ];
}
