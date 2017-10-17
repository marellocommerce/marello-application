<?php

namespace Marello\Bundle\SalesBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Handler\DeleteHandler;

class SalesChannelGroupDeleteHandler extends DeleteHandler
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity SalesChannelGroup */
        parent::checkPermissions($entity, $em);
        if (!$this->securityFacade->isGranted('EDIT', $entity->getOrganization())) {
            throw new ForbiddenException('You have no rights to delete this entity');
        }
        if ($entity->isSystem()) {
            throw new ForbiddenException('It is forbidden to delete system Sales Channel Group');
        }
    }
}
