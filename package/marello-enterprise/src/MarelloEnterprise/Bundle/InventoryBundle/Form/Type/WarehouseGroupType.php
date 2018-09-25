<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class WarehouseGroupType extends AbstractType
{
    const NAME = 'marello_warehouse_group';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $entityId = null;
        $fixedType = false;
        if ($entity instanceof WarehouseGroup) {
            $entityId = $entity->getId();
            if ($entityId) {
                foreach ($entity->getWarehouses() as $warehouse) {
                    $warehouseType = $warehouse->getWarehouseType()->getName();
                    if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED) {
                        $fixedType = true;
                    }
                }
            }
        }

        $builder
            ->add(
                'name',
                TextType::class
            )
            ->add(
                'description',
                TextareaType::class
            )
            ->add(
                'warehouses',
                SystemGroupGlobalWarehouseMultiSelectType::class,
                [
                    'attr' => [
                        'data-entity-id' => $entityId,
                        'disabled' => $fixedType
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WarehouseGroup::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
