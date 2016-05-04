<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemUpdateApiTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryItemUpdateApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryItemApiType extends AbstractType
{
    const NAME = 'marello_inventory_item_api';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock', 'number')
            ->add('warehouse', 'entity', [
                'class' => Warehouse::class,
            ]);

        $builder->addModelTransformer(new InventoryItemUpdateApiTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryItemUpdateApi::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
