<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use Marello\Bundle\InventoryBundle\Entity\WarehouseType as WarehouseTypeEntity;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [WarehouseType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('default', CheckboxType::class, [
                'required' => false,
                'tooltip'  => 'marelloenterprise.inventory.warehouse.delete',
            ])
            ->add('warehouseType', EntityType::class, [
                'label'    => 'marello.inventory.warehouse.warehouse_type.label',
                'class'    => WarehouseTypeEntity::class,
                'required' => true,
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'preSetDataListener']
            );
    }

    /**
     * @param FormEvent $event
     */
    public function preSetDataListener(FormEvent $event)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $event->getData();
        $form = $event->getForm();

        if ($warehouse->getWarehouseType() !== null) {
            FormUtils::replaceField($form, 'warehouseType', ['disabled' => true]);
        }
    }
}
