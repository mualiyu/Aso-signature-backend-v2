<?php

namespace Webkul\Alteration\Repositories;

use Webkul\Alteration\Contracts\AlterationFixType;
use Webkul\Core\Eloquent\Repository;

class AlterationFixTypeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return AlterationFixType::class;
    }

    /**
     * Active fix types in display order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActive()
    {
        return $this->model->where('is_active', true)->orderBy('sort')->get();
    }
}
