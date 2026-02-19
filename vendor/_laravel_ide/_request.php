<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \Webkul\Customer\Models\Customer|null
     */
    public function user($guard = null);
}