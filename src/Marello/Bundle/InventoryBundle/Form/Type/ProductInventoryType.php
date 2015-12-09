<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductInventoryType extends AbstractType
{
    const NAME = 'marello_product_inventory';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('include_variants', $options['include_variants']);

        $builder->add('inventoryItems', InventoryItemCollectionType::NAME);
        if ($builder->getAttribute('include_variants')) {
            $builder->add('variant', VariantInventoryType::NAME);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Product $data */
            $data = $event->getData();

            /*
             * If product has variant, product itself is removed from collection of variant products.
             */
            if ($data && $variant = $data->getVariant()) {
                $variant->getProducts()->removeElement($data);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Product',
            'cascade_validation' => true,
            'include_variants'   => true,
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
