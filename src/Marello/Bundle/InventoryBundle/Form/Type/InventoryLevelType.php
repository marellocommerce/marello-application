<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Marello\Bundle\InventoryBundle\Form\DataTransformer\InventoryLevelModifierTransformer;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelModifier;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class InventoryLevelType extends AbstractType
{
    const NAME = 'marello_inventory_inventorylevel';

    /** @var InventoryLevelModifierTransformer $transformer */
    protected $transformer;

    public function __construct(InventoryLevelModifierTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('warehouse', EntityType::class, [
                    'class' => Warehouse::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('wh')
                            ->where('wh.default = true');
                    },
                    'attr'  => ['readonly' => true]
                ]
            )
            ->add('adjustmentOperator', ChoiceType::class, [
                'choices'            => [
                    InventoryLevelModifier::OPERATOR_INCREASE => 'increase',
                    InventoryLevelModifier::OPERATOR_DECREASE => 'decrease',
                ],
                'translation_domain' => 'MarelloInventoryChangeDirection',
            ])
            ->add('quantity', NumberType::class, [
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
            'data_class'         => InventoryLevelModifier::class,
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
