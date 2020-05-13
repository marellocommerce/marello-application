<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Form\EventSubscriber\MethodConfigSubscriber;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodIconAwareInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodConfigType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_method_config';

    /**
     * @var PaymentMethodProviderInterface
     */
    protected $methodProvider;

    /**
     * @var MethodConfigSubscriber
     */
    protected $subscriber;

    /**
     * @param MethodConfigSubscriber          $subscriber
     * @param PaymentMethodProviderInterface $methodProvider
     */
    public function __construct(
        MethodConfigSubscriber $subscriber,
        PaymentMethodProviderInterface $methodProvider
    ) {
        $this->subscriber = $subscriber;
        $this->methodProvider = $methodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('method', HiddenType::class, ['required' => true]);
        $builder->add('options', HiddenType::class);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $methodsLabels = [];
        $methodsIcons = [];
        /* @var PaymentMethodInterface|PaymentMethodIconAwareInterface $method */
        foreach ($this->methodProvider->getPaymentMethods() as $method) {
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
            'data_class' => PaymentMethodConfig::class,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
