<?php

namespace Marello\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

trait EntityManagerTrait
{
    protected $entityManager;

    /**
     * @return mixed
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return EntityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $entityManager;
    }
}
