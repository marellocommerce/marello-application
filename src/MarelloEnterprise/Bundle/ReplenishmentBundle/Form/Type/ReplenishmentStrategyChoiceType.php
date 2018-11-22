<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentStrategyChoicesProvider;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplenishmentStrategyChoiceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_strategy_choice';

    /**
     * @var ReplenishmentStrategyChoicesProvider
     */
    protected $choicesProvider;

    /**
     * @param ReplenishmentStrategyChoicesProvider $provider
     */
    public function __construct(ReplenishmentStrategyChoicesProvider $provider)
    {
        $this->choicesProvider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => array_flip($this->choicesProvider->getChoices())
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
