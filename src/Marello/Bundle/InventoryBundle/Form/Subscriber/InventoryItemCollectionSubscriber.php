<?php

namespace Marello\Bundle\InventoryBundle\Form\Subscriber;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InventoryItemCollectionSubscriber implements EventSubscriberInterface
{
    /** @var Registry */
    protected $doctrine;

    /**
     * InventoryItemCollectionSubscriber constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['initializeCollection', 100],
            FormEvents::SUBMIT       => 'setProduct',
        ];
    }

    /**
     * Initializes collection of items. Creates new items for warehouses which yet don't exist in it.
     *
     * @param FormEvent $event
     */
    public function initializeCollection(FormEvent $event)
    {
        /** @var Collection|InventoryItem[]|null $items */
        $items = $event->getData();

        if (!$items) {
            $items = new ArrayCollection();
        }

        $items = $this->fillItemCollection($items);

        $event->setData($items);
    }

    /**
     * Fills item collection so it contains all warehouses.
     *
     * @param Collection $items
     *
     * @return Collection
     */
    protected function fillItemCollection(Collection $items)
    {
        $indexed = [];

        /** @var InventoryItem $item */
        foreach ($items as $item) {
            $indexed[$item->getWarehouse()->getId()] = $item;
        }

        $warehouses = $this->doctrine->getRepository('MarelloInventoryBundle:Warehouse')->findAll();

        foreach ($warehouses as $warehouse) {
            if (!array_key_exists($warehouse->getId(), $indexed)) {
                $newItem = new InventoryItem();
                $newItem->setWarehouse($warehouse);

                /*
                 * Add item to collection, collection is not ordered, so any new warehouses will be added to end.
                 * TODO: Solve this by ordering warehouses when retrieving relation on Product entity.
                 */
                $items->add($newItem);
            }
        }

        return $items;
    }

    /**
     * @param FormEvent $event
     */
    public function setProduct(FormEvent $event)
    {
        if ($parent = $event->getForm()->getParent()) {
            $items = $event->getData();
            $product = $parent->getData();

            /** @var InventoryItem $item */
            $items->map(function ($item) use ($product) {
                $item->setProduct($product);
            });
        }
    }
}
