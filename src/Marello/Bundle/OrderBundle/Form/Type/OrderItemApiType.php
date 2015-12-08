<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\DataTransformer\ProductToSkuTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemApiType extends AbstractType
{
    const NAME = 'marello_order_item_api';

    /** @var ProductToSkuTransformer */
    protected $productModelTransformer;

    /**
     * OrderItemApiType constructor.
     *
     * @param ProductToSkuTransformer $productModelTransformer
     */
    public function __construct(ProductToSkuTransformer $productModelTransformer)
    {
        $this->productModelTransformer = $productModelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'text')
            ->add('quantity', 'number')
            ->add('price', 'oro_money')
            ->add('tax', 'oro_money')
            ->add('totalPrice', 'oro_money');

        $builder->get('product')->addModelTransformer($this->productModelTransformer);
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
