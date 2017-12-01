<?php
namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSelectType extends AbstractType
{
    const NAME = 'marello_product_select';
    
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
                    'result_template_twig' => 'MarelloProductBundle:Product:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'MarelloProductBundle:Product:Autocomplete/selection.html.twig',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
