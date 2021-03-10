<?php

namespace Nevadskiy\Geonames\Suppliers;

interface Supplier
{
    /**
     * Insert items into the database.
     *
     * @param iterable|array<int, array> $data
     */
    public function insertMany(iterable $data): void;

    /**
     * Modify items according to the given data.
     *
     * @param iterable|array<int, array> $data
     */
    public function modifyMany(iterable $data): void;

    /**
     * Delete items according to the given data.
     *
     * @param iterable|array<int, array> $data
     */
    public function deleteMany(iterable $data): void;
}
