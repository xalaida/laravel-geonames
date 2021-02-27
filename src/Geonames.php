<?php

namespace Nevadskiy\Geonames;

class Geonames
{
    /**
     * Indicates if Geonames will use a morph map.
     *
     * @return bool
     */
    public static bool $useMorphMap = true;

    /**
     * Disable a morph map for the package.
     */
    public static function disableMorphMap(): void
    {
        static::$useMorphMap = false;
    }
}
