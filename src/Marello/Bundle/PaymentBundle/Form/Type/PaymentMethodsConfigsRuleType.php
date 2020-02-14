<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodChoicesProviderInterface;
use Marello\Bundle\RuleBundle\Form\Type\RuleType;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentMethodsConfigsRuleType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_methods_configs_rule';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var PaymentMethodChoicesProviderInterface
     */
    protected $provider;

    /**
     * @param PaymentMethodChoicesProviderInterface $provider
     * @param TranslatorInterface                   $translator
     */
    public function __construct(
        PaymentMethodChoicesProviderInterface $provider,
        TranslatorInterface $translator
    ) {
        $this->provider = $provider;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', RuleType::class, ['label' => 'marello.payment.paymentmethodsconfigsrule.rule.label'])
            ->add('currency', CurrencySelectionType::class, [
                'label' => 'marello.payment.paymentmethodsconfigsrule.currency.label',
                'placeholder' => 'oro.currency.currency.form.choose',
            ])
            ->add('destinations', CollectionType::class, [
                'required'             => false,
                'entry_type'           => PaymentMethodsConfigsRuleDestinationType::class,
                'label'                => 'marello.payment.paymentmethodsconfigsrule.destinations.label',
                'show_form_when_empty' => false,
            ])
            ->add('methodConfigs', PaymentMethodConfigCollectionType::class, [
                'required' => false,
            ])
            ->add('method', OroChoiceType::class, [
                'mapped' => false,
                'choices' => array_flip($this->provider->getMethods())
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['methods'] = $this->provider->getMethods(true);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethodsConfigsRule::class
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
