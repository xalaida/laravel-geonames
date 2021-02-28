<?php

namespace Nevadskiy\Geonames\Parsers;

class AlternateNameParser extends Parser
{
    /**
     * @inheritDoc
     */
    protected function fieldsMapping(): array
    {
        return [
            'alternateNameId', // the id of this alternate name, int
            'geonameid', //geonameId referring to id in table 'geoname', int
            'isolanguage', // iso 639 language code 2- or 3-characters; 4-characters 'post' for postal codes and 'iata','icao' and faac for airport codes, fr_1793 for French Revolution names,  abbr for abbreviation, link to a website (mostly to wikipedia), wkdt for the wikidataid, varchar(7)
            'alternate name', // alternate name or name variant, varchar(400)
            'isPreferredName', // '1', if this alternate name is an official/preferred name
            'isShortName', // '1', if this is a short name like 'California' for 'State of California'
            'isColloquial', // '1', if this alternate name is a colloquial or slang term. Example: 'Big Apple' for 'New York'.
            'isHistoric', // '1', if this alternate name is historic and was used in the past. Example 'Bombay' for 'Mumbai'.
        ];
    }
}
