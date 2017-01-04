<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

class PartialReceiveType extends AbstractType
{
    const NAME = 'marello_po_partial_receive';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'items',
            PurchaseOrderItemReceiveCollectionType::NAME,
            ['label' => false]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
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
