<?php

namespace Nevadskiy\Geonames\Seeders;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class CompositeSeeder implements Seeder, LoggerAwareInterface
{
    /**
     * The seeder list.
     *
     * @var array|Seeder[]
     */
    protected $seeders;

    /**
     * Make a new seeder instance.
     */
    public function __construct(Seeder ...$seeders)
    {
        $this->seeders = $seeders;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        foreach ($this->seeders as $seeder) {
            if ($seeder instanceof LoggerAwareInterface) {
                $seeder->setLogger($logger);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function seed(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->seed();
        }
    }

    /**
     * @inheritDoc
     */
    public function sync(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->sync();
        }
    }

    /**
     * @inheritDoc
     */
    public function dailyUpdate(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->dailyUpdate();
        }
    }

    /**
     * @inheritDoc
     */
    public function truncate(): void
    {
        foreach (array_reverse($this->seeders) as $seeder) {
            $seeder->truncate();
        }
    }
}
