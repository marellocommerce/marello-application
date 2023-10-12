<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_select';
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'products',
                'create_form_route'  => 'marello_product_create',
                'grid_name' => 'marello-products-extended-no-actions-grid',
                'configs'            => [
                    'placeholder' => 'marello.product.form.choose_product',
                    'result_template_twig' => '@MarelloProduct/Product/Autocomplete/result.html.twig',
                    'selection_template_twig' => '@MarelloProduct/Product/Autocomplete/selection.html.twig',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
