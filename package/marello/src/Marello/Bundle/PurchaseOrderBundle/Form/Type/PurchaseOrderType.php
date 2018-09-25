<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Validator\Constraints\PurchaseOrderConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\FormBundle\Form\Type\OroDateType;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class PurchaseOrderType extends AbstractType
{
    const NAME = 'marello_purchase_order';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                'marello_supplier_select_form',
                [
                    'attr'           => ['readonly' => true],
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
            ->add(
                'items',
                PurchaseOrderItemCollectionType::NAME,
                [
                    'cascade_validation' => true,
                ]
            )
            ->add(
                'dueDate',
                OroDateType::NAME,
                [
                    'required' => false,
                    'label' => 'marello.purchaseorder.due_date.label',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
            'constraints' => [
                new PurchaseOrderConstraint()
            ],
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
