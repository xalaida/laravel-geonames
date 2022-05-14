<?php

namespace Nevadskiy\Geonames\Definitions;

/**
 * @see: http://www.geonames.org/export/codes.html
 */
class FeatureCode
{
    /**
     * Capital of a political entity.
     */
    public const PPLC = 'PPLC';

    /**
     * Seat of a first-order administrative division (PPLC takes precedence over PPLA).
     */
    public const PPLA = 'PPLA';

    /**
     * Seat of a second-order administrative division.
     */
    public const PPLA2 = 'PPLA2';

    /**
     * Seat of a third-order administrative division.
     */
    public const PPLA3 = 'PPLA3';

    /**
     * Section of populated place.
     */
    public const PPLX = 'PPLX';

    /**
     * Seat of government of a political entity.
     */
    public const PPLG = 'PPLG';

    /**
     * Populated place	a city, town, village, or other agglomeration of buildings where people live and work.
     */
    public const PPL = 'PPL';

    /**
     * Independent political entity.
     */
    public const PCLI = 'PCLI';

    /**
     * First-order administrative division.
     */
    public const ADM1 = 'ADM1';

    /**
     * Second-order administrative division.
     */
    public const ADM2 = 'ADM2';

    /**
     * Continent.
     */
    public const CONT = 'CONT';

    /**
     * TODO: add possibility to configure that (using syntax for city seeder)
     * Get cities feature codes.
     *
     * @return string[]
     */
    public static function cities(): array
    {
        return [
            self::PPL,
            self::PPLC,
            self::PPLA,
            self::PPLA2,
            self::PPLA3,
            self::PPLX,
            self::PPLG,
        ];
    }
}
