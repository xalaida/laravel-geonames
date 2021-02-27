<?php

namespace Nevadskiy\Geonames\ValueObjects;

class Location
{
    /**
     * The latitude value of the location.
     */
    private float $latitude;

    /**
     * The longitude value of the location.
     */
    private float $longitude;

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
}
