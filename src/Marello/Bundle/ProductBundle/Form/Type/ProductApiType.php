<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber;
use Marello\Bundle\PricingBundle\Form\Type\AssembledPriceListCollectionType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductApiType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_api_form';
    
    /**
     * @var PricingSubscriber
     */
    protected $pricingSubscriber;
    
    /**
     * @param PricingSubscriber $pricingSubscriber
     */
    public function __construct(PricingSubscriber $pricingSubscriber)
    {
        $this->pricingSubscriber = $pricingSubscriber;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sku')
            ->add('status', EntityType::class, [
                'class' => ProductStatus::class,
            ])
            ->add(
                'weight',
                NumberType::class,
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
                'data_class'         => Product::class,
                'cascade_validation' => true,
                'csrf_protection'    => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
