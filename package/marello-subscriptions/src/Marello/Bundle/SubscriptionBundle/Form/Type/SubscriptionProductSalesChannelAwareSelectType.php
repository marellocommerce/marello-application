<?php
namespace Marello\Bundle\SubscriptionBundle\Form\Type;

use Marello\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionProductSalesChannelAwareSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_subscription_product_sales_channel_aware_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'sales_channel_subscription_products',
                'grid_name' => 'marello-subscription-products-sales-channel-aware-grid',
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
