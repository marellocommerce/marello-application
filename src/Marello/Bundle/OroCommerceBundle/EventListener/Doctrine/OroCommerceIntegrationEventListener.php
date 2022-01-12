<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class OroCommerceIntegrationEventListener
{
    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $salesChannel = new SalesChannel($channel->getName());
            $salesChannel = $this->modifySalesChannel($channel, $salesChannel);

            $em = $args->getEntityManager();

            $group = $this->createOwnGroup($salesChannel, $em);
            $salesChannel->setGroup($group);

            $em->persist($salesChannel);
            $em->flush();
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $em = $args->getEntityManager();

            $salesChannel = $em->getRepository(SalesChannel::class)->findOneBy(['integrationChannel' => $channel]);
            if ($salesChannel) {
                $salesChannel = $this->modifySalesChannel($channel, $salesChannel);

                $em->persist($salesChannel);
                $em->flush();
            }
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function preRemove(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $em = $args->getEntityManager();

            $salesChannel = $em->getRepository(SalesChannel::class)->findOneBy(['integrationChannel' => $channel]);
            if ($salesChannel) {
                $em->getUnitOfWork()->scheduleForDelete($salesChannel);
            }
        }
    }

    /**
     * @param Channel $channel
     * @param SalesChannel $salesChannel
     * @return SalesChannel
     */
    private function modifySalesChannel(Channel $channel, SalesChannel $salesChannel)
    {
        /** @var OroCommerceSettings $transport */
        $transport = $channel->getTransport();
        $salesChannel
            ->setCode(strtolower($channel->getName()))
            ->setChannelType(OroCommerceChannelType::TYPE)
            ->setActive($channel->isEnabled())
            ->setCurrency($transport->getCurrency())
            ->setDefault(true)
            ->setOwner($channel->getOrganization())
            ->setIntegrationChannel($channel);

        return $salesChannel;
    }

    /**
     * @param SalesChannel $entity
     * @param EntityManager $em
     * @return SalesChannelGroup
     */
    private function createOwnGroup(SalesChannel $entity, EntityManager $em)
    {
        $name = $entity->getName();
        $group = new SalesChannelGroup();
        $group
            ->setName($name)
            ->setDescription(sprintf('%s group', $name))
            ->setSystem(false);

        $em->persist($group);
        $em->flush($group);

        return $group;
    }
}
