<?php

namespace Marello\Bundle\ProductBundle\Model;

interface ProductTypeInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getAttributeFamilyCode();
}
