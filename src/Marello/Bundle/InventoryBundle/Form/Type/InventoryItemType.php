<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'replenishment',
                EnumChoiceType::class,
                [
                    'enum_code' => 'marello_inv_reple',
                    'required'  => true,
                    'label'     => 'marello.inventory.inventoryitem.replenishment.label',
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
            );
        if ($this->subscriber !== null) {
            $builder->addEventSubscriber($this->subscriber);
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
