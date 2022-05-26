<?php

namespace Nevadskiy\Geonames\Seeders;

class Report
{
    /**
     * The created records amount.
     */
    protected $created = 0;

    /**
     * The updated records amount.
     */
    protected $updated = 0;

    /**
     * The deleted records amount.
     */
    protected $deleted = 0;

    /**
     * Get the created records amount.
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * Set the created records amount.
     */
    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    /**
     * Increment the created records amount.
     */
    public function incrementCreated(int $created): void
    {
        $this->created += $created;
    }

    /**
     * Get the updated records amount.
     */
    public function getUpdated(): int
    {
        return $this->updated;
    }

    /**
     * Set the updated records amount.
     */
    public function setUpdated(int $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * Increment the updated records amount.
     */
    public function incrementUpdated(int $updated): void
    {
        $this->updated += $updated;
    }

    /**
     * Get the deleted records amount.
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * Set the deleted records amount.
     */
    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * Increment the deleted records amount.
     */
    public function incrementDeleted(int $deleted): void
    {
        $this->deleted += $deleted;
    }
}
