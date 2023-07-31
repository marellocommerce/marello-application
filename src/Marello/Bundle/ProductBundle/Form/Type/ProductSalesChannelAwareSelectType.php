<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSalesChannelAwareSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_sales_channel_aware_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'sales_channel_products',
                'grid_name' => 'marello-products-sales-channel-aware-grid',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        if ($options['configs']['component'] != 'sales-channel-aware') {
            $options['configs']['component'] .= '-sales-channel-aware';
        };
        $options['configs']['extra_config'] = 'sales_channel_aware';
        $view->vars = array_replace_recursive($view->vars, ['configs' => $options['configs']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ProductSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
