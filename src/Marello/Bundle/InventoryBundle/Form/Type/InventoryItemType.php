<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryItemModifyTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryItemModify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class InventoryItemType extends AbstractType
{
    const NAME = 'marello_inventory_item';

    /** @var InventoryItemModifyTransformer $transformer */
    protected $transformer;

    public function __construct(InventoryItemModifyTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockOperator', 'choice', [
                'choices'            => [
                    InventoryItemModify::OPERATOR_INCREASE => 'increase',
                    InventoryItemModify::OPERATOR_DECREASE => 'decrease',
                ],
                'translation_domain' => 'MarelloInventoryChangeDirection',
            ])
            ->add('stock', 'number', [
                'constraints' => new GreaterThanOrEqual(0),
                'data'        => 0,
            ]);

        $builder->addViewTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => InventoryItemModify::class,
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
