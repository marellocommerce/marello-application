<?php

namespace Marello\Bundle\SalesBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DefaultSalesChannelSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager $em */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'preSetData',
            FormEvents::POST_SET_DATA   => 'postSetData'
        ];
    }

    /**
     * Preset data for channels
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $entity = $event->getData();
        if (!$entity || null === $entity->getId()) {
            if ($entity instanceof SalesChannelAwareInterface) {
                $channels = $this->getDefaultChannels();
                if (!is_null($channels) && count($channels) !== 0) {
                    foreach ($channels as $channel) {
                        $entity->addChannel($channel);
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
        $qb = $this->em->getRepository('MarelloSalesBundle:SalesChannel')
            ->createQueryBuilder('sc');

        return $qb
            ->where($qb->expr()->eq('sc.active', $qb->expr()->literal(true)))
            ->andWhere($qb->expr()->eq('sc.default', $qb->expr()->literal(true)))
            ->orderBy('sc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Add channels to hidden add field
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $product = $event->getData();

        if ($product &&
            $product instanceof SalesChannelAwareInterface &&
            !$product->getId() &&
            $product->hasChannels()
        ) {
            $form = $event->getForm();
            if ($form->has('addSalesChannels')) {
                $form->get('addSalesChannels')->setData($product->getChannels());
            }
        }
    }
}
