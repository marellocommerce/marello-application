<?php
namespace Marello\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_customer_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marello_customers',
                'create_form_route'  => 'marello_customer_create',
                'grid_name'          => 'marello-customer-select-grid',
                'create_enabled'     => true,
                'configs'            => [
                    'placeholder' => 'marello.customer.form.choose',
                    'result_template_twig' => '@MarelloCustomer/Customer/Autocomplete/result.html.twig',
                    'selection_template_twig' => '@MarelloCustomer/Customer/Autocomplete/selection.html.twig',
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
