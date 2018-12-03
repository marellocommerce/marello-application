<?php

namespace MarelloEnterprise\Bundle\SalesBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\SalesBundle\Handler\SalesChannelGroupDeleteHandler as BaseDeleteHandler;

class SalesChannelGroupDeleteHandler extends BaseDeleteHandler
{
    /**
     * @var WarehouseChannelGroupLinkRepository $repository
     */
    protected $repository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param WarehouseChannelGroupLinkRepository $repository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        WarehouseChannelGroupLinkRepository $repository
    ) {
        parent::__construct($authorizationChecker);
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
