<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\Valid;

class OrderItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item_collection';

    public function __construct(
        private Router $router
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['grid_url'] = $this->router->generate(
            'marello_order_widget_products_by_channel'
        );
        $view->vars['selector_window_title'] = 'marello.product.entity_plural_label';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => OrderItemType::class,
            'show_form_when_empty' => false,
            'error_bubbling'       => true,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__nameorderitem__',
            'prototype'            => true,
            'handle_primary'       => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
