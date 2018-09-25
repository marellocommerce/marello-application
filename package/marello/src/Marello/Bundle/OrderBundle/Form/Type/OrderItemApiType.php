<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\DataTransformer\ProductToSkuTransformer;
use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemApiType extends AbstractType
{
    const NAME = 'marello_order_item_api';

    /**
     * @var ProductToSkuTransformer
     */
    protected $productModelTransformer;

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $taxCodeModelTransformer;

    /**
     * OrderItemApiType constructor.
     *
     * @param ProductToSkuTransformer $productModelTransformer
     * @param TaxCodeToCodeTransformer $taxCodeModelTransformer
     */
    public function __construct(
        ProductToSkuTransformer $productModelTransformer,
        TaxCodeToCodeTransformer $taxCodeModelTransformer
    ) {
        $this->productModelTransformer = $productModelTransformer;
        $this->taxCodeModelTransformer = $taxCodeModelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'text')
            ->add('productName', 'text')
            ->add('quantity', 'number')
            ->add('originalPriceInclTax', 'oro_money')
            ->add('originalPriceExclTax', 'oro_money')
            ->add('purchasePriceIncl', 'oro_money')
            ->add('price', 'oro_money')
            ->add('tax', 'oro_money')
            ->add('taxCode', 'text')
            ->add('taxPercent', 'number')
            ->add('rowTotalInclTax', 'oro_money')
            ->add('rowTotalExclTax', 'oro_money')
        ;

        $builder->get('product')->addModelTransformer($this->productModelTransformer);
        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\OrderBundle\Entity\OrderItem',
            'cascade_validation' => true,
            'csrf_protection'    => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
