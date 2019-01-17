<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\DataTransformer\ProductToSkuTransformer;
use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Proxies\__CG__\Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class OrderItemApiType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item_api';

    /**
     * @var ProductToSkuTransformer
     */
    protected $productModelTransformer;

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $taxCodeModelTransformer;

    /**
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
            ->add('product', TextType::class)
            ->add('productName', TextType::class)
            ->add('quantity', NumberType::class)
            ->add('originalPriceInclTax', OroMoneyType::class)
            ->add('originalPriceExclTax', OroMoneyType::class)
            ->add('purchasePriceIncl', OroMoneyType::class)
            ->add('price', OroMoneyType::class)
            ->add('tax', OroMoneyType::class)
            ->add('taxCode', TextType::class)
            ->add('taxPercent', NumberType::class)
            ->add('rowTotalInclTax', OroMoneyType::class)
            ->add('rowTotalExclTax', OroMoneyType::class)
        ;

        $builder->get('product')->addModelTransformer($this->productModelTransformer);
        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => OrderItem::class,
            'constraints'        => [new Valid()],
            'csrf_protection'    => false,
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
