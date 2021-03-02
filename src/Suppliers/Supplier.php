<?php

namespace Nevadskiy\Geonames\Suppliers;

interface Supplier
{
    /**
     * Attempt to insert geonames data and return true on success.
     */
    public function insert(array $data, int $id): bool;

    /**
     * Attempt to modify a geonames data by the given id and return true on success.
     */
    public function modify(array $data, int $id): bool;

    /**
     * Attempt to delete a geonames data by the given id and return true if success.
     */
    public function delete(array $data, int $id): bool;
}
