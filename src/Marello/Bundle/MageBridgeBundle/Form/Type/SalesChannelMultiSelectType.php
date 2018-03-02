<?php
namespace Marello\Bundle\MageBridgeBundle\Form\Type;

use Marello\Bundle\SalesBundle\Form\Type\AbstractSalesChannelMultiSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelMultiSelectType extends AbstractSalesChannelMultiSelectType
{
    const NAME = 'marello_sales_saleschannel_multi_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'saleschannels',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marello.sales.saleschannel.form.select_saleschannels',
                'allowClear'  => true
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
