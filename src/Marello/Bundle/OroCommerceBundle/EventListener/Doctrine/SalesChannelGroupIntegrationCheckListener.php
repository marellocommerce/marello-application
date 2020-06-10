<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

class SalesChannelGroupIntegrationCheckListener
{
    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @param LifecycleEventArgs $args
     * @throws AccessDeniedException
     */
    public function preRemove(SalesChannelGroup $salesChannelGroup, LifecycleEventArgs $args)
    {
        if ($this->getIntegrationChannel($salesChannelGroup, $args->getEntityManager())) {
            $message = 'It is forbidden to delete a Sales Channel Group that is still connected to an integration';
            $this->session
                ->getFlashBag()
                ->add('error', $message);
            throw new AccessDeniedException($message);
        }
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @param EntityManager $em
     * @return null|Channel
     */
    private function getIntegrationChannel(
        SalesChannelGroup $salesChannelGroup,
        EntityManager $em
    ) {
        /** @var OroCommerceSettings $transport */
        $transport = $em
            ->getRepository(OroCommerceSettings::class)
            ->findOneBy(['salesChannelGroup' => $salesChannelGroup]);
        if ($transport) {
            return $transport->getChannel();
        }

        return null;
    }
}
