<?php

namespace Marello\Bundle\SalesBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

class DefaultSalesChannelFieldSubscriber implements EventSubscriberInterface
{
    /** @var ConfigManager $configManager */
    protected $configManager;

    /** @var EntityManager $em */
    protected $em;

    /**
     * @param ConfigManager $configManager
     * @param EntityManager $em
     */
    public function __construct(ConfigManager $configManager, EntityManager $em)
    {
        $this->configManager = $configManager;
        $this->em = $em;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        $form   = $event->getForm();
        if (!$entity || null === $entity->getId()) {
            if ($form->has('channels') && $entity instanceof SalesChannelAwareInterface) {
                $channels = $this->getDefaultChannels();
                if (!is_null($channels) && count($channels) !== 0) {
                    foreach ($channels as $_channel) {
                        $entity->addChannel($_channel);
                    }
                }
                $event->setData($entity);
            }
        }
    }

    /**
     * Get default channels for new products.
     * @return array $qb
     */
    public function getDefaultChannels()
    {
        return $this->em->getRepository('MarelloSalesBundle:SalesChannel')
            ->createQueryBuilder('sc')
            ->where('sc.active = 1')
            ->andWhere('sc.default = 1')
            ->orderBy('sc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
