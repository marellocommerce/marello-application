<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Model;

interface GeocodeAwareInterface
{
    /**
     * @return string
     */
    public function getLatitude();

    /**
     * @return string
     */
    public function getLongitude();
}
