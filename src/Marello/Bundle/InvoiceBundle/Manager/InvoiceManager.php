<?php

namespace Marello\Bundle\InvoiceBundle\Manager;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Mapper\MapperInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InvoiceManager
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
        $this->entityManager = $doctrineHelper->getEntityManagerForClass(Invoice::class);
    }

    /**
     * @param Order $sourceEntity
     */
    public function createInvoice(Order $sourceEntity)
    {
        $existingInvoice = $this->entityManager
            ->getRepository(Invoice::class)
            ->findBy(
                [
                    'order' => $sourceEntity
                ]
            );

        if (!$existingInvoice) {
            $invoice = $this->mapper->map($sourceEntity);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush($invoice);
        }
    }
}
