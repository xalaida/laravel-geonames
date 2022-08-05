<?php

namespace Nevadskiy\Geonames\Support;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class OutputFactory
{
    /**
     * Make a new output style instance.
     */
    public static function make(int $verbosity = OutputInterface::VERBOSITY_NORMAL): OutputStyle
    {
        $output = new OutputStyle(new StringInput(''), new StreamOutput(fopen('php://stdout', 'wb')));

        $output->setVerbosity($verbosity);

        return $output;
    }
}
