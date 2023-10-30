<?php

namespace Marello\Bundle\CoreBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnClearEventArgs;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\SecurityBundle\EventListener\RefreshContextListener as ParentListener;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * Fix issue with "Proxy interface given instead of class manager",
 * it occurs because EntityManager was made as lazy in https://github.com/doctrine/DoctrineBundle/pull/559
 */
class RefreshContextListener extends ParentListener
{
    /**
     * @param OnClearEventArgs                $event
     * @param OrganizationAwareTokenInterface $token
     */
    protected function checkOrganization(OnClearEventArgs $event, OrganizationAwareTokenInterface $token)
    {
        $organization = $token->getOrganization();
        if (!is_object($organization)) {
            return;
        }
        $organizationClass = ClassUtils::getClass($organization);
        if ($event->getEntityClass() && $event->getEntityClass() !== $organizationClass) {
            return;
        }
        /**
         * Start customization
         */
        $em = $event->getObjectManager();
        $classEm = $this->doctrine->getManagerForClass($organizationClass);
        if ($classEm instanceof VirtualProxyInterface) {
            $classEm = $classEm->getWrappedValueHolderValue();
        }
        if ($em !== $classEm) {
            return;
        }
        /**
         * End customization
         */
        $organization = $this->refreshEntity($organization, $organizationClass, $em);
        if (!$organization) {
            return;
        }
        $token->setOrganization($organization);
    }
}
