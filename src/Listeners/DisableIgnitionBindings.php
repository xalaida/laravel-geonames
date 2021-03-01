<?php

namespace Nevadskiy\Geonames\Listeners;

use Facade\Ignition\QueryRecorder\QueryRecorder;

class DisableIgnitionBindings
{
    /**
     * Ignition query recorded instance.
     *
     * @var QueryRecorder
     */
    private $queryRecorder;

    /**
     * DisableIgnitionBindings constructor.
     */
    public function __construct(QueryRecorder $queryRecorder)
    {
        $this->queryRecorder = $queryRecorder;
    }

    /**
     * Fix ignition a memory leak problem.
     */
    public function handle(): void
    {
        $this->queryRecorder->setReportBindings(false);
    }
}
