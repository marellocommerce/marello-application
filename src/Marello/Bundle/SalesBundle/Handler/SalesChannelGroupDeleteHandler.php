<?php

namespace Marello\Bundle\SalesBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

use Oro\Bundle\SoapBundle\Handler\DeleteHandler;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class SalesChannelGroupDeleteHandler extends DeleteHandler
{
    /**
     * @var AuthorizationChecker $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
        /** @var $entity SalesChannelGroup */
        parent::checkPermissions($entity, $em);
        if (!$this->authorizationChecker->isGranted('EDIT', $entity->getOrganization())) {
            throw new ForbiddenException('You have no rights to delete this entity');
        }
        if ($entity->isSystem()) {
            throw new ForbiddenException('It is forbidden to delete system Sales Channel Group');
        }
    }
}
