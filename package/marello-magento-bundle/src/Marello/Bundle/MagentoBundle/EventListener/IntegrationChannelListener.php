<?php

namespace Marello\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use Marello\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Marello\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class IntegrationChannelListener
{
    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postPersist(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() !== MagentoChannelType::TYPE) {
            return $this;
        }
        
        $salesChannel = new SalesChannel($channel->getName());
        $salesChannel = $this->modifySalesChannel($channel, $salesChannel);

        $em = $args->getEntityManager();

        $group = $this->createOwnGroup($salesChannel, $em);
        $salesChannel->setGroup($group);

        $em->persist($salesChannel);
        $em->flush();
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUpdate(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() !== MagentoChannelType::TYPE) {
            return $this;
        }
        $em = $args->getEntityManager();

        $salesChannel = $em->getRepository(SalesChannel::class)->findOneBy(['integrationChannel' => $channel]);
        if ($salesChannel) {
            $salesChannel = $this->modifySalesChannel($channel, $salesChannel);

            $em->persist($salesChannel);
            $em->flush();
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function preRemove(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() !== MagentoChannelType::TYPE) {
            return $this;
        }
        
        $em = $args->getEntityManager();

        $salesChannel = $em->getRepository(SalesChannel::class)->findOneBy(['integrationChannel' => $channel]);
        if ($salesChannel) {
            $em->remove($salesChannel);
            $em->flush();
        }
    }

    /**
     * @param Channel $channel
     * @param SalesChannel $salesChannel
     * @return SalesChannel
     */
    private function modifySalesChannel(Channel $channel, SalesChannel $salesChannel)
    {
        /** @var MagentoSoapTransport $transport */
        $transport = $channel->getTransport();
        
        $salesChannel
            ->setCode(str_replace(" ", "_", strtolower($channel->getName())))
            ->setChannelType(MagentoChannelType::TYPE)
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
     * @throws \Doctrine\ORM\OptimisticLockException
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
