<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryItemCollectionType extends AbstractType
{
    const NAME = 'marello_inventory_item_collection';

    /** @var EventSubscriberInterface */
    protected $formSubscriber;

    /**
     * InventoryItemCollectionType constructor.
     *
     * @param EventSubscriberInterface $formSubscriber
     */
    public function __construct(EventSubscriberInterface $formSubscriber)
    {
        $this->formSubscriber = $formSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber($this->formSubscriber);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add'          => false,
            'allow_delete'       => false,
            'type'               => InventoryItemType::NAME,
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }
}
