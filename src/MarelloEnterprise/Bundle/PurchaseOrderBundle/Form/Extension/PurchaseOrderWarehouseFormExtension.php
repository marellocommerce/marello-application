<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepTwoType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\InventoryLevelWarehouseSelectType;

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
            InventoryLevelWarehouseSelectType::class,
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
    public static function getExtendedTypes(): iterable
    {
        return [PurchaseOrderCreateStepTwoType::class];
    }
}
