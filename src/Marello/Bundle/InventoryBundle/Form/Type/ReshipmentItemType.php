<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventoryConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReshipmentItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_reshipment_item';

    public function __construct(
        protected TranslatorInterface $translator,
        protected AvailableInventoryProvider $availableInventoryProvider,
        protected array $addQuantityErrorByForm = [],
        protected array $addAvailableQuantityErrorByForm = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productSku', TextType::class, [
                'label' => 'marello.product.sku.label',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('productName', TextType::class, [
                'label' => 'marello.product.entity_label',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('availableQuantity', NumberType::class, [
                'label' => 'marello.order.orderitem.ordered.label',
                'attr' => [
                    'readonly' => true
                ],
                'mapped' => false,
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'marello.order.orderitem.ordered.label',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('newQuantity', NumberType::class, [
                'label' => 'marello.order.orderitem.quantity.label',
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'fillAvailableQuantity']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'changeItemQuantity']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'addQuantityError']);
    }

    public function fillAvailableQuantity(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var OrderItem $orderItem */
        $orderItem = $event->getData();
        $availableInventory = $this->availableInventoryProvider->getAvailableInventory(
            $orderItem->getProduct(),
            $orderItem->getOrder()->getSalesChannel()
        );
        $form->get('availableQuantity')->setData($availableInventory);
    }

    public function changeItemQuantity(FormEvent $event)
    {
        $data = $event->getData();
        if ($data['newQuantity'] > $data['quantity']) {
            $this->addQuantityErrorByForm[] = $event->getForm();
        } elseif ($data['newQuantity'] > $data['availableQuantity']) {
            $this->addAvailableQuantityErrorByForm[] = $event->getForm();
        } else {
            $data['quantity'] = $data['newQuantity'];
            $event->setData($data);
        }
    }

    public function addQuantityError(FormEvent $event)
    {
        $quantityForm = $event->getForm()->get('newQuantity');
        foreach ($this->addQuantityErrorByForm as $key => $form) {
            if ($event->getForm() !== $form) {
                continue;
            }

            $quantityForm->addError(new FormError(
                $this->translator->trans('marello.inventory.reshipment.quantity.validation_error')
            ));
            unset($this->addQuantityErrorByForm[$key]);
        }

        foreach ($this->addAvailableQuantityErrorByForm as $key => $form) {
            if ($event->getForm() !== $form) {
                continue;
            }

            $quantityForm->addError(new FormError(
                $this->translator->trans('marello.inventory.reshipment.available_quantity.validation_error')
            ));
            unset($this->addAvailableQuantityErrorByForm[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
            'constraints' => [
                new AvailableInventoryConstraint(['fields' => ['quantity', 'order', 'product']])
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
