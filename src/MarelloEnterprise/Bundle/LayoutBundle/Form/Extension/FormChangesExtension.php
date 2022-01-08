<?php

namespace MarelloEnterprise\Bundle\LayoutBundle\Form\Extension;

use Marello\Bundle\RefundBundle\Form\Type\RefundType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractTypeExtension;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType;

class FormChangesExtension extends AbstractTypeExtension
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($view->vars['options'])) {
            $view->vars['options'] = ['viewOptions' => []];
        } elseif (!isset($view->vars['options']['viewOptions'])) {
            $view->vars['options']['viewOptions'] = [];
        }

        $organization = $this->tokenAccessor->getOrganization();
        $view->vars['options']['viewOptions']['isGlobalOrg'] = $organization && $organization->getIsGlobal();
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [OrderType::class, PaymentCreateType::class];
    }
}
