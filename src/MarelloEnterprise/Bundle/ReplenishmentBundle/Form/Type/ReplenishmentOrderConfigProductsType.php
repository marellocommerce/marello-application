<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;

class ReplenishmentOrderConfigProductsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_config_products';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'added',
            EntityIdentifierType::class,
            [
                'class' => $options['class'],
                'multiple' => true,
                'mapped' => false,
            ]
        );
        $builder->add(
            'removed',
            EntityIdentifierType::class,
            [
                'class' => $options['class'],
                'multiple' => true,
                'mapped' => false,
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['class']);
        $resolver->setDefaults(
            [
                'add_acl_resource'           => null,
                'class'                      => 'MarelloProductBundle:Product',
                'default_element'            => null,
                'initial_elements'           => null,
                'selector_window_title'
                    => 'marelloenterprise.replenishment.form.replenishmentorderconfig.products.selector_window.title',
                'extra_config'               => null,
                'selection_url'              => null,
                'selection_route'            => null,
                'selection_route_parameters' => [],
            ]
        );
    }


    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->setOptionToView($view, $options, 'extra_config');
        $this->setOptionToView($view, $options, 'grid_url'); // deprecated
        $this->setOptionToView($view, $options, 'selection_url');
        $this->setOptionToView($view, $options, 'selection_route');
        $this->setOptionToView($view, $options, 'selection_route_parameters');
        $this->setOptionToView($view, $options, 'initial_elements');
        $this->setOptionToView($view, $options, 'selector_window_title');
        $this->setOptionToView($view, $options, 'default_element');

        if (empty($options['add_acl_resource'])) {
            $options['allow_action'] = true;
        } else {
            $options['allow_action'] = $this->authorizationChecker->isGranted($options['add_acl_resource']);
        }

        $this->setOptionToView($view, $options, 'allow_action');
    }

    /**
     * @param FormView $view
     * @param array    $options
     * @param string   $option
     */
    protected function setOptionToView(FormView $view, array $options, $option)
    {
        $view->vars[$option] = isset($options[$option]) ? $options[$option] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
