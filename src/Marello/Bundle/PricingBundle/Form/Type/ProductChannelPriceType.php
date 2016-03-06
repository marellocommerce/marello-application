<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChannelPriceType extends AbstractType
{
    const NAME = 'marello_product_channel_price';

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
        $channels = $options['channels'];

        $builder
            ->add('channel', 'genemu_jqueryselect2_entity', [
                'class' => 'MarelloSalesBundle:SalesChannel',
                'query_builder' => function(EntityRepository $er) use ($channels) {
                    return $er->createQueryBuilder('sc')
                        ->where('sc.id IN(:channels)')
                        ->setParameter('channels', $channels);
                },
            ])
            ->add('currency', 'hidden', [
                'required' => true,
                'data'     => $this->localeSettings->getCurrency(),
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
        $currencyCode   = $this->localeSettings->getCurrency();
        $currencySymbol = $this->localeSettings->getCurrencySymbolByCurrency($currencyCode);

        $resolver->setDefaults([
            'data_class'        => 'Marello\Bundle\PricingBundle\Entity\ProductChannelPrice',
            'intention'         => 'productchannelprice',
            'single_form'       => true,
            'currency'          => $currencyCode,
            'currency_symbol'   => $currencySymbol,
            'channels'          => []
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['currency']        = $options['currency'];
        $view->vars['currency_symbol'] = $options['currency_symbol'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
