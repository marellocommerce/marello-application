<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\CurrencyBundle\Form\DataTransformer\MoneyValueTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductPriceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_price';

    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', HiddenType::class, [
                'required' => true,
            ])
            ->add('startDate', OroDateTimeType::class, [
                'required' => false,
            ])
            ->add('endDate', OroDateTimeType::class, [
                'required' => false
            ]);

        if ($options['currency'] && $options['currency_symbol']) {
            $builder
                ->add('value', OroMoneyType::class, [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                    'currency' => $options['currency'],
                    'currency_symbol' => $options['currency_symbol']
                ]);
        } else {
            $builder
                ->add('value', OroMoneyType::class, [
                    'required' => false,
                    'label' => 'marello.pricing.productprice.value.label',
                ]);
        }

        $builder->get('value')->addModelTransformer(new MoneyValueTransformer());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    public function postSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof ProductPrice
            && $data->getStartDate()
            && $data->getEndDate()
            && $data->getStartDate() > $data->getEndDate()
        ) {
            $event->getForm()->get('startDate')->addError(new FormError(
                $this->translator->trans('marello.pricing.productprice.start.validation.error')
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ProductPrice::class,
            'intention'         => 'productprice',
            'single_form'       => true,
            'currency'          => null,
            'currency_symbol'   => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
