<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;

use Marello\Bundle\InventoryBundle\Provider\BalancingStrategyChoicesProvider;

class BalancerStrategyChoiceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_balancer_strategy_choice';

    /**
     * @var BalancingStrategyChoicesProvider
     */
    protected $choicesProvider;

    /**
     * @param BalancingStrategyChoicesProvider $provider
     */
    public function __construct(BalancingStrategyChoicesProvider $provider)
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
