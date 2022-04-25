<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryLevelFinishFormViewEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InventoryLevelType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_inventorylevel';

    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * InventoryLevelType constructor.
     * @param EventSubscriberInterface $subscriber
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventSubscriberInterface $subscriber,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->subscriber = $subscriber;
        $this->eventDispatcher = $eventDispatcher;
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
                    'attr'  => ['readonly' => true]
                ]
            )
            ->add(
                'pickLocation',
                TextType::class,
                [
                    'required' => false,
                    'label'    => 'marello.inventory.inventorylevel.pick_location.label'
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
            )
            ->add(
                'managedInventory',
                CheckboxType::class
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
            'display' => true,
            'constraints' => [
                new Valid()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->eventDispatcher->dispatch(
            new InventoryLevelFinishFormViewEvent($view),
            InventoryLevelFinishFormViewEvent::NAME
        );
        $vars = $view->vars;
        $value = $vars['value'];
        if ($value instanceof InventoryLevel) {
            $warehouseType = $value->getWarehouse()->getWarehouseType()->getName();
            if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                $vars['externalWarehouse'] = true;
            } else {
                $vars['externalWarehouse'] = false;
            }
            $isInventoryBatchEnabled = $value->getInventoryItem()->isEnableBatchInventory();
            if ($isInventoryBatchEnabled &&
                $warehouseType !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                $vars['manageBatches'] = true;
            } else {
                $vars['manageBatches'] = false;
            }
        } else {
            $vars['externalWarehouse'] = false;
            $vars['manageBatches'] = false;
        }
        $view->vars = $vars;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
