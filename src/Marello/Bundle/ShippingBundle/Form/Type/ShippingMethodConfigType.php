<?php

namespace Marello\Bundle\ShippingBundle\Form\Type;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Form\EventSubscriber\MethodConfigSubscriber;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodIconAwareInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodConfigType extends AbstractType
{
    const NAME = 'marello_shipping_method_config';

    /**
     * @var ShippingMethodProviderInterface
     */
    protected $shippingMethodProvider;

    /**
     * @var MethodConfigSubscriber
     */
    protected $subscriber;

    /**
     * @param MethodConfigSubscriber          $subscriber
     * @param ShippingMethodProviderInterface $shippingMethodProvider
     */
    public function __construct(
        MethodConfigSubscriber $subscriber,
        ShippingMethodProviderInterface $shippingMethodProvider
    ) {
        $this->subscriber = $subscriber;
        $this->shippingMethodProvider = $shippingMethodProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('method', HiddenType::class, ['required' => false]);
        $builder->add('typeConfigs', ShippingMethodTypeConfigCollectionType::class);
        $builder->add('options', HiddenType::class);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $methodsLabels = [];
        $methodsIcons = [];
        /* @var ShippingMethodInterface|ShippingMethodIconAwareInterface $method */
        foreach ($this->shippingMethodProvider->getShippingMethods() as $method) {
            $methodsLabels[$method->getIdentifier()] = $method->getLabel();
            $methodsIcons[$method->getIdentifier()] = $method->getIcon();
        }
        $view->vars['methods_labels'] = $methodsLabels;
        $view->vars['methods_icons'] = $methodsIcons;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethodConfig::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
