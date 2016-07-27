<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderItemReceiveType extends AbstractType
{
    const NAME = 'marello_purchase_order_item_receive';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('receive_amount', 'integer', [
            'mapped' => false,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Collection|PurchaseOrderItem $data */
            $data = $event->getData();
            /** @var int */
            $receiveAmount = $event->getForm()->get('receive_amount')->getData();

            $data->setReceivedAmount($data->getReceivedAmount() + $receiveAmount);

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderItem::class,
            'error_bubbling' => true,
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
}
