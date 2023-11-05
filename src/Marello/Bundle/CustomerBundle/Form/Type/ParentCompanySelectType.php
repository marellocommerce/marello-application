<?php

namespace Marello\Bundle\CustomerBundle\Form\Type;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentCompanySelectType extends AbstractType
{
    const NAME = 'marello_customer_parent_company_select';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marello_company_parent',
                'configs' => [
                    'component' => 'autocomplete-entity-parent',
                    'placeholder' => 'marello.customer.company.form.choose_parent'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();
        $companyId = null;
        if ($parentData instanceof Company) {
            $companyId = $parentData->getId();
        }
        $view->vars['configs']['entityId'] = $companyId;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroJquerySelect2HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
