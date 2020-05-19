<?php

namespace Nevadskiy\Geonames\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait Slug
{
    /**
     * Boot the slug trait.
     *
     * @return void
     */
    public static function bootSlug(): void
    {
        self::creating(static function (self $model) {
            $model->setSlugIfEmpty();
        });
    }

    /**
     * Generate a slug from the given source string.
     *
     * @return string
     */
    public static function generateSlug(string $source): string
    {
        return Str::slug($source);
    }

    /**
     * Set the model slug if it is empty.
     */
    protected function setSlugIfEmpty(): void
    {
        if (! $this->getSlugKey()) {
            $this->setSlug();
        }
    }

    /**
     * Get the slug key.
     *
     * @return string|null
     */
    public function getSlugKey(): ?string
    {
        return $this->getAttribute($this->getSlugKeyName());
    }

    /**
     * Get the slug key name.
     *
     * @return string
     */
    public function getSlugKeyName(): string
    {
        return 'slug';
    }

    /**
     * Set the model slug.
     */
    protected function setSlug(): void
    {
        $this->setAttribute($this->getSlugKeyName(), static::generateSlug($this->getSlugSourceKey()));
    }

    /**
     * Get the slug source key.
     *
     * @return string|null
     */
    public function getSlugSourceKey(): string
    {
        return $this->getAttribute($this->getSlugSourceKeyName());
    }

    /**
     * Get the slug source key name.
     *
     * @return string
     */
    public function getSlugSourceKeyName(): string
    {
        return 'name';
    }
}