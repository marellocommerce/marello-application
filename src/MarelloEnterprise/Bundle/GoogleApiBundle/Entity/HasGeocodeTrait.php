<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasGeocodeTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=50, nullable=true)
     */
    protected $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=50, nullable=true)
     */
    protected $longitude;

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param string|null $latitude
     * @return object
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string|null $longitude
     * @return object
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }
}
