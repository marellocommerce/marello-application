<?php

namespace MarelloEnterprise\Bundle\SalesBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Handler\SalesChannelGroupDeleteHandler as BaseDeleteHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class SalesChannelGroupDeleteHandler extends BaseDeleteHandler
{
    /**
     * @var WarehouseChannelGroupLinkRepository
     */
    protected $repository;

    /**
     * @param SecurityFacade $securityFacade
     * @param WarehouseChannelGroupLinkRepository $repository
     */
    public function __construct(SecurityFacade $securityFacade, WarehouseChannelGroupLinkRepository $repository)
    {
        parent::__construct($securityFacade);
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity SalesChannelGroup */
        parent::checkPermissions($entity, $em);
        $linkOwner = $this->repository->findLinkBySalesChannelGroup($entity);
        if ($linkOwner && !$linkOwner->isSystem()) {
            throw new \Exception('It is forbidden to delete Linked Sales Channel(Group), unlink it first');
        }
    }
}
