<?php
namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSupplierAdviceSelectType extends AbstractType
{
    const NAME = 'marello_product_supplier_advice_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'products',
                'create_form_route'  => 'marello_product_create',
                'grid_name' => 'marello-purchase-order-item-candidates',
                'grid_parameters' => [],
                'configs'            => [
                    'multiple'  => true,
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
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $supplierId = $form->getParent()->getParent()->getParent()->get('supplier')->getData()->getId();

        $view->vars['grid_parameters'] = ['supplierId' => $supplierId];
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
