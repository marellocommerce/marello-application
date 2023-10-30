<?php

namespace Marello\Bundle\CustomerBundle\Form\Type;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanySelectType extends AbstractType
{
    const NAME = 'marello_customer_company_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marello_customer_company',
                'grid_name' => 'marello-companies-select-grid',
                'entity_class'          => Company::class,
                'create_enabled'        => true,
                'create_form_route' => 'marello_customer_company_create',
                'configs' => [
                    'placeholder' => 'marello.customer.company.form.choose',
                ],
                'attr' => [
                    'class' => 'marello-customer-company-select',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }
}
