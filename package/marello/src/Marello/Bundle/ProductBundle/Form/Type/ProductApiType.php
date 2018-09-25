<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber;
use Marello\Bundle\PricingBundle\Form\Type\AssembledPriceListCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductApiType extends AbstractType
{
    const NAME = 'marello_product_api_form';
    
    /**
     * @var PricingSubscriber
     */
    protected $pricingSubscriber;
    
    /**
     * @param PricingSubscriber $pricingSubscriber
     */
    public function __construct(
        PricingSubscriber $pricingSubscriber
    ) {
        $this->pricingSubscriber         = $pricingSubscriber;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sku')
            ->add('status', 'entity', [
                'class' => 'Marello\Bundle\ProductBundle\Entity\ProductStatus',
            ])
            ->add(
                'weight',
                'number',
                [
                    'required' => false,
                    'scale' => 2,
                ]
            )
            ->add('prices', AssembledPriceListCollectionType::class)
            ->add('channels');
        
        $builder->addEventSubscriber($this->pricingSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Product',
                'cascade_validation' => true,
                'csrf_protection'    => false,
            ]
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
