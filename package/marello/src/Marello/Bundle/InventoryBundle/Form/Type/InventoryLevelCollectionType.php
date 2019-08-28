<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class InventoryLevelCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_inventorylevel_collection';

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
            'entry_type'           => InventoryLevelType::class,
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__nameinventorylevelcollection__',
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
