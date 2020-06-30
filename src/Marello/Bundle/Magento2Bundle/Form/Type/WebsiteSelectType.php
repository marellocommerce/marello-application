<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteSelectType extends AbstractType
{
    private const BLOCK_PREFIX = 'marello_magento2_website_select';

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
        $resolver->setDefaults([
            'label' => 'marello.magento2.website_form.website.label',
            'attr' => [
                'data-role' => 'website-select',
                'data-page-component-name' => 'websiteSelectComponent',
                'data-page-component-module' => 'marellomagento2/js/app/components/website-component'
            ],
            'configs' => [
                'placeholder' => 'marello.magento2.website_form.website.placeholder',
                'allowClear' => false,
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configs'] = $options['configs'];
    }
}
