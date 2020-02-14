<?php

namespace Marello\Bundle\ShippingBundle\Form\Type;

use Marello\Bundle\RuleBundle\Form\Type\RuleType;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Provider\ShippingMethodChoicesProviderInterface;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencySelectionType;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShippingMethodsConfigsRuleType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_shipping_methods_configs_rule';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ShippingMethodChoicesProviderInterface
     */
    protected $provider;

    /**
     * @param ShippingMethodChoicesProviderInterface $provider
     * @param TranslatorInterface                    $translator
     */
    public function __construct(
        ShippingMethodChoicesProviderInterface $provider,
        TranslatorInterface $translator
    ) {
        $this->provider = $provider;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', RuleType::class, ['label' => 'marello.shipping.shippingmethodsconfigsrule.rule.label'])
            ->add('currency', CurrencySelectionType::class, [
                'label' => 'marello.shipping.shippingmethodsconfigsrule.currency.label',
                'placeholder' => 'oro.currency.currency.form.choose',
            ])
            ->add('destinations', CollectionType::class, [
                'required'             => false,
                'entry_type'           => ShippingMethodsConfigsRuleDestinationType::class,
                'label'                => 'marello.shipping.shippingmethodsconfigsrule.destinations.label',
                'show_form_when_empty' => false,
            ])
            ->add('methodConfigs', ShippingMethodConfigCollectionType::class, [
                'required' => false,
            ])
            ->add('method', OroChoiceType::class, [
                'mapped' => false,
                'choices' => array_flip($this->provider->getMethods())
            ]);
    }

    /**
     * {@inheritdoc}
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
            'data_class' => ShippingMethodsConfigsRule::class,
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
