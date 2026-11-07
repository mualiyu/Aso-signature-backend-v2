<?php

namespace Webkul\Alteration;

/**
 * The storefront master switch (Admin → Configure → Sales → Alterations). While it is off,
 * customers see nothing of the programme; the admin side stays available so partners, caps and
 * claims can be prepared before launch.
 */
class AlterationFeature
{
    public const CONFIG_KEY = 'sales.alteration.settings.enabled';

    public static function enabled(): bool
    {
        return (bool) core()->getConfigData(self::CONFIG_KEY);
    }
}
