<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use Marello\Bundle\ProductBundle\Form\Type\ProductSelectType;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseSelectType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderManualItemConfig;
use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplenishmentOrderManualItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_manual_item';

    public function __construct(
        protected TranslatorInterface $translator,
        protected $addQuantityErrorByForm = []
    ) {}

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'product',
                ProductSelectType::class,
                [
                    'required' => true,
                    'create_enabled' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderitem.product.label',
                ]
            )
            ->add(
                'origin',
                WarehouseSelectType::class,
                [
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderitem.source.label',
                    'constraints' => new NotNull()
                ]
            )
            ->add(
                'quantity',
                NumberType::class,
                [
                    'required' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderitem.quantity.label',
                ]
            )
            ->add(
                'allQuantity',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderitem.all.label',
                ]
            )
            ->add(
                'availableQuantity',
                NumberType::class,
                [
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
                ]
            )
            ->add(
                'unit',
                TextType::class,
                [
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
                    'mapped' => false,
                ]
            )
            ->add(
                'destination',
                WarehouseSelectType::class,
                [
                    'required' => true,
                    'label' => 'marelloenterprise.replenishment.replenishmentorderitem.destination.label',
                    'constraints' => new NotNull()
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'compareAvailability']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'addQuantityError']);
    }

    public function compareAvailability(FormEvent $event)
    {
        $data = $event->getData();
        if (!empty($data['allQuantity']) || empty($data['quantity']) || empty($data['availableQuantity'])) {
            return;
        }

        if ($data['quantity'] > $data['availableQuantity']) {
            $this->addQuantityErrorByForm[] = $event->getForm();
        }
    }

    public function addQuantityError(FormEvent $event)
    {
        foreach ($this->addQuantityErrorByForm as $key => $form) {
            if ($event->getForm() === $form) {
                $event->getForm()->get('quantity')->addError(new FormError(
                    $this->translator->trans('marelloenterprise.replenishment.form.replenishmentorderconfig.add_product.quantity.validation_error')
                ));
                unset($this->addQuantityErrorByForm[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReplenishmentOrderManualItemConfig::class,
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
