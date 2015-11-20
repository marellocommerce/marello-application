<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemModifyTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryItemModify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryItemType extends AbstractType
{
    const NAME = 'marello_inventory_item';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modifyOperator', 'choice', [
                'choices' => [
                    InventoryItemModify::OPERATOR_INCREASE => 'Increase',
                    InventoryItemModify::OPERATOR_DECREASE => 'Decrease',
                ],
            ])
            ->add('modifyAmount', 'number');

        $builder->addModelTransformer(new InventoryItemModifyTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\InventoryBundle\Model\InventoryItemModify',
            'cascade_validation' => true,
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
