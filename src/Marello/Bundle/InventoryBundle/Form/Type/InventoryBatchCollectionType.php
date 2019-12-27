<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class InventoryBatchCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_inventorybatch_collection';

    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    /**
     * @param EventSubscriberInterface|null $subscriber
     */
    public function __construct(EventSubscriberInterface $subscriber = null)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->subscriber !== null) {
            $builder->addEventSubscriber($this->subscriber);
        }
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => InventoryBatchType::class,
            'allow_add'            => false,
            'allow_remove'         => false,
            'show_form_when_empty' => true,
            'error_bubbling'       => false,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__nameinventorybatchcollection__',
            'prototype'            => true,
            'handle_primary'       => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
