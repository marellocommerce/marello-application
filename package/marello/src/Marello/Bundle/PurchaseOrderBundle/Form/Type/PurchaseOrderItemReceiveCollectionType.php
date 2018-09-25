<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Doctrine\ORM\PersistentCollection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderItemReceiveCollectionType extends AbstractType
{
    const NAME = 'marello_purchase_order_item_receive_collection';

    /**
     * {@inheritdoc}
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var PersistentCollection $collection */
            $collection = $event->getData();
            $collection->map(
                function (PurchaseOrderItem $item) {
                    if ($item->getReceivedAmount() === $item->getOrderedAmount()) {
                        $item->setStatus('complete');
                    }
                }
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'                  => PurchaseOrderItemReceiveType::NAME,
            'show_form_when_empty'  => false,
            'error_bubbling'        => true,
            'cascade_validation'    => true,
            'prototype_name'        => '__namepurchaseorderitemreceive__',
            'prototype'             => true,
            'handle_primary'        => false,
            'allow_add'             => false,
            'allow_delete'          => false,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::NAME;
    }
}
