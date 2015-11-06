<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelFieldSubscriber;
use Marello\Bundle\PricingBundle\Form\EventListener\PricesCollectionFieldSubscriber;

class ProductType extends AbstractType
{
    const NAME = 'marello_product_form';

    /** @var ConfigManager */
    protected $configManager;

    /** @var EntityManager */
    protected $em;

    /** @var LocaleSettings $localeSettings */
    protected $localeSettings;

    public function __construct(ConfigManager $configManager, EntityManager $em, LocaleSettings $localeSettings)
    {
        $this->configManager = $configManager;
        $this->em = $em;
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'channels',
            'marello_sales_saleschannel_select',
            [
                'label' => 'marello.sales.saleschannel.entity_label'
            ]
        );

        $builder->addEventSubscriber(new DefaultSalesChannelFieldSubscriber($this->configManager, $this->em));
        $builder
        ->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.name.label'
            ]
        )
        ->add(
            'sku',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.sku.label'
            ]
        )
        ->add(
            'price',
            'oro_money',
            [
                'required' => true,
                'label' => 'marello.product.price.label'
            ]
        )
        ->add(
            'status',
            'entity',
            array(
                'label' => 'marello.product.status.label',
                'class' => 'MarelloProductBundle:ProductStatus',
                'property' => 'label',
                'required' => true,
            )
        )
        ->add(
            'stockLevel',
            'text',
            [
                'required' => true,
                'label'    => 'marello.product.stock_level.label',
                'read_only'    => true
            ]
        )
        ->add('prices',
            'marello_product_price_collection'
        );
        $builder->addEventSubscriber(new PricesCollectionFieldSubscriber($this->localeSettings));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Marello\\Bundle\\ProductBundle\\Entity\\Product',
                  'intention' => 'product',
                  'single_form' => true,
                )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
