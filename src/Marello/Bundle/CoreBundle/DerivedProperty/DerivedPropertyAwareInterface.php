<?php

namespace Marello\Bundle\CoreBundle\DerivedProperty;

interface DerivedPropertyAwareInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setDerivedProperty($id);
}
