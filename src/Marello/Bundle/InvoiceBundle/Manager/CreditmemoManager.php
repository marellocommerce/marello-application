<?php

namespace Marello\Bundle\InvoiceBundle\Manager;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Mapper\MapperInterface;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CreditmemoManager
{
    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @param MapperInterface $mapper
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        MapperInterface $mapper,
        DoctrineHelper $doctrineHelper
    ) {
        $this->mapper = $mapper;
        $this->entityManager = $doctrineHelper->getEntityManagerForClass(Creditmemo::class);
    }

    /**
     * @param Refund $sourceEntity
     */
    public function createCreditmemo(Refund $sourceEntity)
    {
        $creditmemo = $this->mapper->map($sourceEntity);
        $this->entityManager->persist($creditmemo);
        $this->entityManager->flush($creditmemo);
    }
}
