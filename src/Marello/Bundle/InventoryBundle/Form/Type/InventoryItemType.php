<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Valid;

class InventoryItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_item';

    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @param EventSubscriberInterface|null $subscriber
     */
    public function __construct(EventSubscriberInterface $subscriber = null)
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
                'orderOnDemandAllowed',
                CheckboxType::class,
                [
                    'required' => false,
                    'tooltip'  => 'marello.inventory.form.tooltip.order_on_demand',
                    'label' => 'marello.inventory.inventoryitem.order_on_demand.label'
                ]
            )
            ->add(
                'backorderAllowed',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.backorder_allowed.label'
                ]
            )
            ->add(
                'maxQtyToBackorder',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.max_qty_to_backorder.label',
                    'tooltip'  => 'marello.inventory.form.tooltip.max_qty_to_backorder'
                ]
            )
            ->add(
                'backOrdersDatetime',
                OroDateTimeType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.back_orders_datetime.label',
                    'tooltip'  => 'marello.inventory.form.tooltip.backorder_datetime'
                ]
            )
            ->add(
                'canPreorder',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.can_preorder.label'
                ]
            )
            ->add(
                'maxQtyToPreorder',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.max_qty_to_preorder.label',
                    'tooltip'  => 'marello.inventory.form.tooltip.max_qty_to_preorder'
                ]
            )
            ->add(
                'preOrdersDatetime',
                OroDateTimeType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.pre_orders_datetime.label',
                    'tooltip'  => 'marello.inventory.form.tooltip.preorder_datetime'
                ]
            )
            ->add(
                'desiredInventory',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0)
                ]
            )
            ->add(
                'purchaseInventory',
                NumberType::class,
                [
                    'constraints' => new GreaterThanOrEqual(0)
                ]
            )
            ->add(
                'inventoryLevels',
                InventoryLevelCollectionType::class
            )
            ->add(
                'enableBatchInventory',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.enable_batch_inventory.label'
                ]
            )
            ->add(
                'productUnit',
                EnumChoiceType::class,
                [
                    'enum_code' => 'marello_product_unit',
                    'required'  => false,
                    'label'     => 'marello.inventory.inventoryitem.product_unit.label',
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetDataListener']);
        if ($this->subscriber !== null) {
            $builder->addEventSubscriber($this->subscriber);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preSetDataListener(FormEvent $event)
    {
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $event->getData();
        $form = $event->getForm();

        if ($inventoryItem->isEnableBatchInventory()) {
            $form->remove('enableBatchInventory');
            $form->add(
                'enableBatchInventory',
                CheckboxType::class,
                [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'marello.inventory.inventoryitem.enable_batch_inventory.label'
                ]
            );
        } else {
            $form->remove('orderOnDemandAllowed');
            $form->add(
                'orderOnDemandAllowed',
                CheckboxType::class,
                [
                    'disabled' => true,
                    'required' => false,
                    'tooltip'  => 'marello.inventory.form.tooltip.order_on_demand',
                    'label' => 'marello.inventory.inventoryitem.order_on_demand.label'
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryItem::class,
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
