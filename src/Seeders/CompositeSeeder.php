<?php

namespace Nevadskiy\Geonames\Seeders;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class CompositeSeeder implements Seeder, LoggerAwareInterface
{
    /**
     * @var array|Seeder[]
     */
    private $seeders;

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

    public function seed(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->seed();
        }
    }

    public function sync(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->sync();
        }
    }

    public function dailyUpdate(): void
    {
        foreach ($this->seeders as $seeder) {
            $seeder->dailyUpdate();
        }
    }

    public function truncate(): void
    {
        foreach (array_reverse($this->seeders) as $seeder) {
            $seeder->dailyUpdate();
        }
    }
}
