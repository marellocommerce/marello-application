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
use Symfony\Component\Validator\Constraints\Valid;

class PurchaseOrderItemReceiveCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order_item_receive_collection';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var PersistentCollection $collection */
            $collection = $event->getData();
            $collection->map(
                function (PurchaseOrderItem $item) {
                    if ($item->getReceivedAmount() === $item->getOrderedAmount()) {
                        $item->setStatus(PurchaseOrderItem::STATUS_COMPLETE);
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
            'entry_type'            => PurchaseOrderItemReceiveType::class,
            'show_form_when_empty'  => false,
            'error_bubbling'        => true,
            'constraints'           => [new Valid()],
            'prototype_name'        => '__namepurchaseorderitemreceive__',
            'prototype'             => true,
            'handle_primary'        => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
