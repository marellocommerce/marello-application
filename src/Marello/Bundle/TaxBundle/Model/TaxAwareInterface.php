<?php

namespace Marello\Bundle\TaxBundle\Model;

interface TaxAwareInterface
{
    /**
     * @return int
     */
    public function getTax();
}
