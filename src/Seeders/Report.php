<?php

namespace Nevadskiy\Geonames\Seeders;

class Report
{
    protected $created = 0;

    protected $updated = 0;

    protected $deleted = 0;

    public function getCreated(): int
    {
        return $this->created;
    }

    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    public function incrementCreated(int $created): void
    {
        $this->created += $created;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function setUpdated(int $updated): void
    {
        $this->updated = $updated;
    }

    public function incrementUpdated(int $updated): void
    {
        $this->updated = $updated;
    }

    public function getDeleted(): int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function incrementDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }
}
