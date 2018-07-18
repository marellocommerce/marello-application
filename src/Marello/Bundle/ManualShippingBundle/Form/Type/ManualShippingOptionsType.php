<?php

namespace Marello\Bundle\ManualShippingBundle\Form\Type;

use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ManualShippingOptionsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_manual_shipping_options_type';

    /**
     * @var RoundingServiceInterface
     */
    protected $roundingService;

    /**
     * @param RoundingServiceInterface $roundingService
     */
    public function __construct(RoundingServiceInterface $roundingService)
    {
        $this->roundingService = $roundingService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $priceOptions = [
            'scale' => $this->roundingService->getPrecision(),
            'rounding_mode' => $this->roundingService->getRoundType(),
            'attr' => ['data-scale' => $this->roundingService->getPrecision()],
        ];

        $builder
            ->add(ManualShippingMethodType::PRICE_OPTION, NumberType::class, array_merge([
                'required' => true,
                'label' => 'marello.manual_shipping.method.price.label',
                'constraints' => [new NotBlank(), new Type(['type' => 'numeric'])]
            ], $priceOptions))
            ->add(ManualShippingMethodType::HANDLING_FEE_OPTION, NumberType::class, array_merge([
                'label' => 'marello.manual_shipping.method.handling_fee.label',
                'constraints' => [new Type(['type' => 'numeric'])]
            ], $priceOptions))
            ->add(ManualShippingMethodType::TYPE_OPTION, ChoiceType::class, [
                'required' => true,
                'choices' => [
                    ManualShippingMethodType::PER_ITEM_TYPE
                    => 'marello.manual_shipping.method.processing_type.per_item.label',
                    ManualShippingMethodType::PER_ORDER_TYPE
                    => 'marello.manual_shipping.method.processing_type.per_order.label',
                ],
                'label' => 'marello.manual_shipping.method.processing_type.label',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'marello.manual_shipping.form.marello_manual_shipping_options_type.label',
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
