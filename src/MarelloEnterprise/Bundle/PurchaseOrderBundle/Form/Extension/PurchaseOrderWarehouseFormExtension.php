<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Extension;

use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepTwoType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseSelectType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class PurchaseOrderWarehouseFormExtension extends AbstractTypeExtension
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'warehouse',
            WarehouseSelectType::class,
            [
                'label' => 'marello.purchaseorder.warehouse.label',
                'required' => true,
                'constraints' => new NotNull()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return PurchaseOrderCreateStepTwoType::class;
    }
}
