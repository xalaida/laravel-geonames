<?php

namespace Nevadskiy\Geonames\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait Uuid
{
    /**
     * Generate uuid during the model creating.
     *
     * @return void
     */
    public static function bootUuid(): void
    {
        self::creating(static function (self $model) {
            $model->generateId();
        });
    }

    /**
     * Generate the ID.
     */
    public function generateId(): void
    {
        $this->{$this->getKeyName()} = Str::uuid()->toString();
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}