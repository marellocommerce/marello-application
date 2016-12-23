<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;

class InventoryManager implements InventoryManagerInterface
{
    /** @var ObjectManager $entityManager */
    protected $entityManager;

    /**
     * InventoryManager constructor.
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->entityManager = $om;
    }

    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        if (!$this->validateItems($context)) {
            throw new \Exception('Item structure not valid.');
        }

        $items = $context->getItems();
        /** @var InventoryItem $item */
        foreach ($items as $data) {
            $stock = null;
            $allocatedStock = null;
            if ($context->getStock()) {
                $stock = ($data['item']->getStock() + $context->getStock());
            }

            if ($context->getAllocatedStock()) {
                $allocatedStock = ($data['item']->getAllocatedStock() + $context->getAllocatedStock());
            }

            $success = $this->updateInventoryLevel(
                $data['item'],
                $context->getChangeTrigger(),
                $stock,
                $allocatedStock,
                $context->getUser(),
                $context->getRelatedEntity()
            );

            if ($success) {
                //$this->entityManager->persist($data['item']);
            }
        }

        //$this->entityManager->flush();
    }

    /**
     * @param InventoryItem     $item           InventoryItem to be updated
     * @param string            $trigger        Action that triggered the change
     * @param int|null          $stock          New stock or null if it should remain unchanged
     * @param int|null          $allocatedStock New allocated stock or null if it should remain unchanged
     * @param User|null         $user           User who triggered the change, if left null,
     *                                          it is automatically assigned to current one
     * @param mixed|null        $subject        Any entity that should be associated to this operation
     *
     * @throws \Exception
     * @return bool
     */
    protected function updateInventoryLevel(
        InventoryItem $item,
        $trigger,
        $stock = null,
        $allocatedStock = null,
        User $user = null,
        $subject = null
    ) {
        if (($stock === null) && ($allocatedStock === null)) {
            return false;
        }

        if (($item->getStock() === $stock) && ($item->getAllocatedStock() === $allocatedStock)) {
            return false;
        }

        if ($stock === null) {
            $stock = $item->getStock();
        }

        if ($allocatedStock === null) {
            $allocatedStock = $item->getAllocatedStock();
        }

        try {
            $item->changeCurrentLevel(new StockLevel(
                $item,
                $stock,
                $allocatedStock,
                $trigger,
                $user,
                $subject
            ));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Validate the data structure of the items to be updated
     * @param $context
     * @return bool
     */
    private function validateItems($context)
    {
        $items = $context->getItems();
        foreach ($items as $item) {
            if (!is_array($item)) {
                return false;
            }

            if (!array_key_exists('item', $item)) {
                return false;
            }

            if (!array_key_exists('qty', $item)) {
                return false;
            }

            if (!array_key_exists('allocatedQty', $item)) {
                return false;
            }
        }

        return true;
    }
}
