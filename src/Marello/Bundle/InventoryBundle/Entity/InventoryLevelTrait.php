<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

trait InventoryLevelTrait
{
    /**
     * @var InventoryLevel
     */
    protected $currentLevel = null;

    /**
     * @param InventoryLevel $newLevel
     *
     * @return $this
     */
    public function changeCurrentLevel(InventoryLevel $newLevel)
    {
        return $this->updateInventoryLevel($newLevel);
    }

    public function updateInventoryLevel($level)
    {
        $this->addInventoryLevel($level);

        return $this;
    }

    /**
     * @return InventoryLevel
     */
    public function getCurrentLevel()
    {
        return $this->currentLevel;
    }

    /**
     * @return Collection|InventoryLevel[]
     */
    public function getLevels()
    {
        return $this->inventoryLevels;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->currentLevel ? $this->currentLevel->getStock() : 0;
    }

    /**
     * @return int
     */
    public function getAllocatedStock()
    {
        return $this->currentLevel ? $this->currentLevel->getAllocatedStock() : 0;
    }

    /**
     * @return int
     */
    public function getVirtualStock()
    {
        return $this->getStock() - $this->getAllocatedStock();
    }
}
