<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Valid;

class InventoryBatchType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_inventorybatch';

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
                'batchNumber',
                TextType::class,
                [
                    'required' => false,
                    'disabled' => true,
                    'label'    => 'marello.inventory.inventorybatch.batch_number.label'
                ]
            )
            ->add(
                'adjustmentOperator',
                ChoiceType::class,
                [
                    'choices' => [
                        'increase' => InventoryLevelCalculator::OPERATOR_INCREASE,
                        'decrease' => InventoryLevelCalculator::OPERATOR_DECREASE,
                    ],
                    'translation_domain' => 'MarelloInventoryChangeDirection',
                    'mapped' => false
                ]
            )
            ->add(
                'adjustmentQuantity',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0),
                    'data'        => 0,
                    'mapped' => false
                ]
            )
            ->add(
                'quantity',
                NumberType::class,
                [
                    'disabled' => true
                ]
            )
            ->add(
                'deliveryDate',
                OroDateType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'purchasePrice',
                NumberType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'sellByDate',
                OroDateTimeType::class,
                [
                    'required' => false
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
            'data_class' => InventoryBatch::class,
            'display' => true,
            'constraints' => [
                new Valid()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
