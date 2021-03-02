<?php

namespace Nevadskiy\Geonames\Suppliers;

interface Supplier
{
    /**
     * Attempt to insert geonames data and return true on success.
     */
    public function insert(array $data, int $id): bool;

    /**
     * Attempt to update a geonames data by the given id if exists or insert a new one and return true on success.
     */
    public function updateOrInsert(array $data, int $id): bool;

    /**
     * Attempt to delete a geonames data by the given id and return true if success.
     */
    public function delete(array $data, int $id): bool;
}
