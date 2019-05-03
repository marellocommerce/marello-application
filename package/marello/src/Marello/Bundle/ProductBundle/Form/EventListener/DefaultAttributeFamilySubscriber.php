<?php

namespace Marello\Bundle\ProductBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadDefaultAttributeFamilyData;

class DefaultAttributeFamilySubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
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
            FormEvents::PRE_SET_DATA    => 'preSetData'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();

        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$product->getAttributeFamily()) {
            $defaultAttributeFamily = $this->em
                ->getRepository(AttributeFamily::class)
                ->findOneBy(['code' => LoadDefaultAttributeFamilyData::DEFAULT_FAMILY_CODE]);
            $product->setAttributeFamily($defaultAttributeFamily);
        }

        $event->setData($product);
    }
}
