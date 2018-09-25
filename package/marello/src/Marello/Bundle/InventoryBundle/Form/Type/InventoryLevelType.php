<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class InventoryLevelType extends AbstractType
{
    const NAME = 'marello_inventory_inventorylevel';

    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function __construct(EventSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'warehouse',
                EntityType::class,
                [
                    'class' => Warehouse::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('wh')
                            ->where('wh.default = true');
                    },
                    'attr'  => ['readonly' => true]
                ]
            )
            ->add(
                'adjustmentOperator',
                ChoiceType::class,
                [
                    'choices' =>
                        [
                            InventoryLevelCalculator::OPERATOR_INCREASE => 'increase',
                            InventoryLevelCalculator::OPERATOR_DECREASE => 'decrease',
                        ],
                    'translation_domain' => 'MarelloInventoryChangeDirection',
                    'mapped' => false
                ]
            )
            ->add(
                'quantity',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0),
                    'data'        => 0,
                    'mapped' => false
                ]
            )
            ->add(
                'inventoryQty',
                NumberType::class,
                [
                    'disabled' => true
                ]
            );

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryLevel::class,
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
    
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
