<?php

namespace Nevadskiy\Geonames\ValueObjects;

use JsonSerializable;

class Location implements JsonSerializable
{
    /**
     * The latitude value of the location.
     *
     * @var float
     */
    protected $latitude;

    /**
     * The longitude value of the location.
     *
     * @var float
     */
    protected $longitude;

    /**
     * Make a new location instance.
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the latitude value of the location.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Get the longitude value of the location.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Determine if the locations are equal.
     */
    public function equals(self $that): bool
    {
        return $this->getLatitude() === $that->getLatitude()
            && $this->getLongitude() === $that->getLongitude();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }
}
