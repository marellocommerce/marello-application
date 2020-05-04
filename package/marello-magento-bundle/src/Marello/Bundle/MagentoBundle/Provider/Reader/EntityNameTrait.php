<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

trait EntityNameTrait
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param $entityName
     * @return $this
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }
}
