<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPriceType extends AbstractType
{
    const NAME = 'marello_product_price';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', 'hidden', [
                'required' => true,
            ])
            ->add('value', 'oro_money', [
                'required' => true,
                'label'    => 'marello.pricing.productprice.value.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => 'Marello\Bundle\PricingBundle\Entity\ProductPrice',
            'intention'         => 'productprice',
            'single_form'       => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
