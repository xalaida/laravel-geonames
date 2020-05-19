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
     * Boot the uuid trait.
     *
     * @return void
     */
    public static function bootUuid(): void
    {
        self::creating(static function (self $model) {
            $model->setKey();
        });
    }

    /**
     * Generate a key.
     *
     * @return string
     */
    public static function generateKey(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Set the model key.
     */
    protected function setKey(): void
    {
        $this->setAttribute($this->getKeyName(), static::generateKey());
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