<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \Webkul\Customer\Models\Customer|null
     */
    public function user();
}