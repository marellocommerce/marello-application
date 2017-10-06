<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\RuleBundle\Form\Type\RuleType;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WFARuleType extends AbstractType
{
    const NAME = 'marello_inventory_wfarule_type';

    /**
     * @var WFAStrategyChoicesProvider
     */
    protected $choicesProvider;

    /**
     * @var WFARuleRepository
     */
    private $wfaRuleRepository;

    /**
     * @param WFAStrategyChoicesProvider $provider
     * @param WFARuleRepository $wfaRuleRepository
     */
    public function __construct(WFAStrategyChoicesProvider $provider, WFARuleRepository $wfaRuleRepository)
    {
        $this->choicesProvider = $provider;
        $this->wfaRuleRepository = $wfaRuleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rule', RuleType::class, [
                'label' => 'marelloenterprise.inventory.wfarule.rule.label',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetDataListener']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetDataListener(FormEvent $event)
    {
        /** @var WFARule $wfaRule */
        $wfaRule = $event->getData();
        $usedStrategies = [];
        foreach ($this->wfaRuleRepository->getUsedStrategies() as $v) {
            if ($v['strategy'] !== $wfaRule->getStrategy()) {
                $usedStrategies[] = $v['strategy'];
            }
        }

        $choices = [];
        foreach ($this->choicesProvider->getChoices() as $identifier => $label) {
            if (!in_array($identifier, $usedStrategies)) {
                $choices[$identifier] = $label;
            }
        }
        $form = $event->getForm();
        $form->add('strategy', ChoiceType::class, [
            'choices' => $choices,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['strategies'] = $this->choicesProvider->getChoices();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WFARule::class,
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
