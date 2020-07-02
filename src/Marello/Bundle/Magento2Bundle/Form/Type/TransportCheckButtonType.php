<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportCheckButtonType extends ButtonType
{
    private const BLOCK_PREFIX = 'marello_magento2_transport_check_button';

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['selectorForFieldsRequiredReCheckConnection']);
        $resolver->setDefaults(['attr' => ['class' => 'btn btn-primary']]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = \array_replace_recursive($view->vars, [
            'component_options' => [
                'selectorForFieldsRequiredReCheckConnection' => $options['selectorForFieldsRequiredReCheckConnection']
            ]
        ]);
    }
}
