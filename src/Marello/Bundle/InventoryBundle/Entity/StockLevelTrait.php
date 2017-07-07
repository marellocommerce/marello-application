<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

trait StockLevelTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLevel", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="current_level_id", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=30,
     *              "full"=true
     *          }
     *      }
     * )
     *
     * @var InventoryLevel
     */
    protected $currentLevel = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryLevel",
     *     mappedBy="inventoryItem",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var InventoryLevel[]|Collection
     */
    protected $levels;

    /**
     * @param InventoryLevel $newLevel
     *
     * @return $this
     */
    public function changeCurrentLevel(InventoryLevel $newLevel)
    {
        $this->levels->add($newLevel);
        $this->currentLevel = $newLevel;

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
        return $this->levels;
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
