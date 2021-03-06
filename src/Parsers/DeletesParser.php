<?php

namespace Nevadskiy\Geonames\Parsers;

class DeletesParser extends Parser
{
    /**
     * @inheritDoc
     */
    protected function fieldsMapping(): array
    {
        return [
            'geonameid',
            'name',
            'comment',
        ];
    }
}
