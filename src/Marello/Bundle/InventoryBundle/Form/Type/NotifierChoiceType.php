<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;

use Marello\Bundle\InventoryBundle\Provider\WarehouseNotifierChoicesProvider;

class NotifierChoiceType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_warehouse_notifier_choice';

    /**
     * @var WarehouseNotifierChoicesProvider
     */
    protected $choicesProvider;

    /**
     * @param WarehouseNotifierChoicesProvider $provider
     */
    public function __construct(WarehouseNotifierChoicesProvider $provider)
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
