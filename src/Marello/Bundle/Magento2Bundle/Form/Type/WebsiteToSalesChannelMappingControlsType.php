<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteToSalesChannelMappingControlsType extends AbstractType
{
    private const BLOCK_PREFIX = 'marello_magento2_website_to_sales_channel_controls';

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salesChannel', SalesChannelInGroupSelectType::class);
        $builder->add('website', WebsiteSelectType::class);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['selectorSalesChannelGroup']);
        $resolver->setRequired(['selectorWebsiteToSalesChannelMapping']);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['component_options'] = \array_merge(
            $view->vars['component_options'] ?? [],
            [
                'selectorSalesChannelGroup' => $options['selectorSalesChannelGroup'],
                'selectorWebsiteToSalesChannelMapping' => $options['selectorWebsiteToSalesChannelMapping'],
            ]
        );
    }
}
