<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \Webkul\Customer\Models\Customer|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \Webkul\Customer\Models\Customer|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \Webkul\Customer\Models\Customer|null
     */
    public static function getUser();

    /**
     * @return \Webkul\Customer\Models\Customer
     */
    public static function authenticate();

    /**
     * @return \Webkul\Customer\Models\Customer|null
     */
    public static function user();

    /**
     * @return \Webkul\Customer\Models\Customer|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \Webkul\Customer\Models\Customer
     */
    public static function getLastAttempted();
}