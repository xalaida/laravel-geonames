<?php

namespace Nevadskiy\Geonames\Definitions;

/**
 * @see: http://www.geonames.org/export/codes.html
 */
class FeatureCode
{
    /**
     * A country, state, region, ...
     */

    /**
     * First-order administrative division.
     */
    public const ADM1 = 'ADM1';

    /**
     * Historical first-order administrative division.
     */
    public const ADM1H = 'ADM1H';

    /**
     * Second-order administrative division.
     */
    public const ADM2 = 'ADM2';

    /**
     * Historical second-order administrative division.
     */
    public const ADM2H = 'ADM2H';

    /**
     * Third-order administrative division.
     */
    public const ADM3 = 'ADM3';

    /**
     * Historical third-order administrative division.
     */
    public const ADM3H = 'ADM3H';

    /**
     * Fourth-order administrative division.
     */
    public const ADM4 = 'ADM4';

    /**
     * Historical fourth-order administrative division.
     */
    public const ADM4H = 'ADM4H';

    /**
     * Fifth-order administrative division.
     */
    public const ADM5 = 'ADM5';

    /**
     * Historical fifth-order administrative division.
     */
    public const ADM5H = 'ADM5H';

    /**
     * Administrative division.
     */
    public const ADMD = 'ADMD';

    /**
     * Historical administrative division.
     */
    public const ADMDH = 'ADMDH';

    /**
     * Leased area.
     */
    public const LTER = 'LTER';

    /**
     * Political entity.
     */
    public const PCL = 'PCL';

    /**
     * Dependent political entity.
     */
    public const PCLD = 'PCLD';

    /**
     * Freely associated state.
     */
    public const PCLF = 'PCLF';

    /**
     * Historical political entity	a former political entity.
     */
    public const PCLH = 'PCLH';

    /**
     * Independent political entity.
     */
    public const PCLI = 'PCLI';

    /**
     * Section of independent political entity.
     */
    public const PCLIX = 'PCLIX';

    /**
     * Semi-independent political entity.
     */
    public const PCLS = 'PCLS';

    /**
     * Parish.
     */
    public const PRSH = 'PRSH';

    /**
     * Territory.
     */
    public const TERR = 'TERR';

    /**
     * Zone.
     */
    public const ZN = 'ZN';

    /**
     * Buffer zone.
     */
    public const ZNB = 'ZNB';

    /**
     * P city, village, ...
     */

    /**
     * Populated place.
     */
    public const PPL = 'PPL';

    /**
     * Seat of a first-order administrative division.
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
     * Seat of a fourth-order administrative division.
     */
    public const PPLA4 = 'PPLA4';

    /**
     * Seat of a fifth-order administrative division.
     */
    public const PPLA5 = 'PPLA5';

    /**
     * Capital of a political entity.
     */
    public const PPLC = 'PPLC';

    /**
     * Historical capital of a political entity.
     */
    public const PPLCH = 'PPLCH';

    /**
     * Farm village.
     */
    public const PPLF = 'PPLF';

    /**
     * Seat of government of a political entity.
     */
    public const PPLG = 'PPLG';

    /**
     * Historical populated place.
     */
    public const PPLH = 'PPLH';

    /**
     * Populated locality.
     */
    public const PPLL = 'PPLL';

    /**
     * Abandoned populated place.
     */
    public const PPLQ = 'PPLQ';

    /**
     * Religious populated place.
     */
    public const PPLR = 'PPLR';

    /**
     * Populated places.
     */
    public const PPLS = 'PPLS';

    /**
     * Destroyed populated place.
     */
    public const PPLW = 'PPLW';

    /**
     * Section of populated place.
     */
    public const PPLX = 'PPLX';

    /**
     * Israeli settlement.
     */
    public const STLMT = 'STLMT';

    /**
     * Others.
     */

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
