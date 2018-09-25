<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Form\EventListener\ChangeSalesChannelGroupSubscriber;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseChannelGroupLinkType extends AbstractType
{
    const NAME = 'marello_warehouse_channel_group_link';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        if ($entity instanceof WarehouseChannelGroupLink) {
            $entityId = $entity->getId();
        } else {
            $entityId = null;
        }
        
        $builder
            ->add(
                'warehouseGroup',
                NotLinkedWarehouseGroupAutocompleteType::class,
                [
                    'label' => 'marello.inventory.warehousechannelgrouplink.warehouse_group.label',
                    'tooltip'   => 'marelloenterprise.inventory.warehousechannelgrouplink.select_warehousegroup',
                    'grid_parameters' => ['linkOwner' => $entityId],
                    'attr' => [
                        'data-entity-id' => $entityId
                    ]
                ]
            )
            ->add(
                'addSalesChannelGroups',
                EntityIdentifierType::class,
                [
                    'class'    => SalesChannelGroup::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeSalesChannelGroups',
                EntityIdentifierType::class,
                [
                    'class'    => SalesChannelGroup::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );
        $builder->addEventSubscriber(new ChangeSalesChannelGroupSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WarehouseChannelGroupLink::class,
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
