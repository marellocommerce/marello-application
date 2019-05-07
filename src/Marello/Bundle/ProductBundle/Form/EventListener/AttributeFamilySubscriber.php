<?php

namespace Marello\Bundle\ProductBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Provider\ProductTypesProvider;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AttributeFamilySubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ProductTypesProvider
     */
    protected $productTypesProvider;

    /**
     * @param EntityManager $em
     * @param ProductTypesProvider $productTypesProvider
     */
    public function __construct(EntityManager $em, ProductTypesProvider $productTypesProvider)
    {
        $this->em = $em;
        $this->productTypesProvider = $productTypesProvider;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        /** @var Product $product */
        $product = $event->getData();
        $form = $event->getForm();
        $type = $form->get('type')->getData();

        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$product->getAttributeFamily() && $type) {
            $productType = $this->productTypesProvider->getProductType($type);
            if ($productType) {
                $attributeFamily = $this->em
                    ->getRepository(AttributeFamily::class)
                    ->findOneBy(['code' => $productType->getAttributeFamilyCode()]);
                $product->setAttributeFamily($attributeFamily);
            }
        }

        $event->setData($product);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data['type']) {
            $productType = $this->productTypesProvider->getProductType($data['type']);
            if ($productType) {
                $attributeFamily = $this->em
                    ->getRepository(AttributeFamily::class)
                    ->findOneBy(['code' => $productType->getAttributeFamilyCode()]);
                $data['attributeFamily'] = $attributeFamily;
            }

            $event->setData($data);
        }
    }
}
