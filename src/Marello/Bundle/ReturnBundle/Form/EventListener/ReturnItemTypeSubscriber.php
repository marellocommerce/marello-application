<?php

namespace Marello\Bundle\ReturnBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReturnItemTypeSubscriber implements EventSubscriberInterface
{
    protected $entityManager;

    /**
     * ReturnItemTypeSubscriber constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT       => 'onPostSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /** @var ReturnItem $returnItem */
        $returnItem = $event->getData();

        if (!$returnItem->getReason()) {
            $returnItem->setReason($this->findDefaultReason());
        }

        $event->setData($returnItem);
    }

    private function findDefaultReason()
    {
        $returnReasonClass = ExtendHelper::buildEnumValueClassName('marello_return_reason');
        $reason = $this->entityManager->getRepository($returnReasonClass)->findOneByDefault(true);

        if (!$reason) {
            $reason = $this->entityManager->getRepository($returnReasonClass)->findOneBy([]);
        }

        return $reason;
    }
}
