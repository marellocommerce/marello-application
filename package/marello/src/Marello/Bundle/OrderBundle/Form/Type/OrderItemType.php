<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Marello\Bundle\OrderBundle\Form\EventListener\OrderItemPurchasePriceSubscriber;
use Marello\Bundle\ProductBundle\Form\Type\ProductSalesChannelAwareSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    const NAME = 'marello_order_item';

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $taxCodeModelTransformer;

    /**
     * @param TaxCodeToCodeTransformer $taxCodeModelTransformer
     */
    public function __construct(TaxCodeToCodeTransformer $taxCodeModelTransformer)
    {
        $this->taxCodeModelTransformer = $taxCodeModelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ProductSalesChannelAwareSelectType::class, [
                'required'       => true,
                'label'          => 'marello.product.entity_label',
                'create_enabled' => false,
            ])
            ->add('quantity', 'number', [
                'data' => 1,
            ])->add('availableInventory', NumberType::class, [
                'mapped' => false,
                'read_only' => true
            ])
            ->add('price', 'text', [
                'read_only' => true,
            ])
            ->add('tax', 'text', [
                'read_only' => true,
            ])
            ->add('taxCode', 'text', [
                'read_only' => true,
            ])
            ->add('rowTotalExclTax', 'text', [
                'read_only' => true,
            ])
            ->add('rowTotalInclTax', 'text', [
                'read_only' => true,
            ])
        ;

        $builder->get('taxCode')->addModelTransformer($this->taxCodeModelTransformer);
        $builder->addEventSubscriber(new OrderItemPurchasePriceSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\OrderBundle\Entity\OrderItem'
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
