<?php
namespace Marello\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyAwareCustomerSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_company_aware_customer_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marello_company_customers',
                'grid_name'          => 'marello-company-customers-select-grid',
                'attr' => [
                    'class' => 'marello-company-aware-customer-select',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        if ($options['configs']['component'] != 'company_customer') {
            $options['configs']['component'] .= '-company-customer';
        };
        $options['configs']['extra_config'] = 'company_customer';
        $view->vars = array_replace_recursive($view->vars, ['configs' => $options['configs']]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomerSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
