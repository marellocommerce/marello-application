<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PurchaseOrderType extends AbstractType
{
    const NAME = 'marello_purchase_order';
    const VALIDATION_MESSAGE = 'Purchase Order must contain at least one item';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                'marello_supplier_select_form',
                [
                    'read_only'      => true,
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
                'dueDate', 'oro_date', [
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
                new Callback(function (PurchaseOrder $purchaseOrder, ExecutionContextInterface $context) {
                    if ($purchaseOrder->getItems()->count() === 0) {
                        $context
                            ->buildViolation(self::VALIDATION_MESSAGE)
                            ->atPath('items')
                            ->addViolation()
                        ;
                    }
                })
            ]
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
