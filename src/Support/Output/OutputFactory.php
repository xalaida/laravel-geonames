<?php

namespace Nevadskiy\Geonames\Support\Output;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class OutputFactory
{
    /**
     * Make the output style.
     */
    public static function make(): OutputStyle
    {
        return new OutputStyle(new StringInput(''), new StreamOutput(fopen('php://stdout', 'wb')));
    }
}
