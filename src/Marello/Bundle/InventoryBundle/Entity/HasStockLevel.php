<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\UserBundle\Entity\User;

trait HasStockLevel
{
    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\StockLevel", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=30,
     *              "full"=true
     *          }
     *      }
     * )
     *
     * @var StockLevel
     */
    protected $currentLevel = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\InventoryBundle\Entity\StockLevel",
     *     mappedBy="inventoryItem",
     *     cascade={"persist", "remove"}
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     *
     * @var StockLevel[]|Collection
     */
    protected $levels;

    /**
     * @param StockLevel $newLevel
     *
     * @return $this
     */
    public function changeCurrentLevel(StockLevel $newLevel)
    {
        $this->levels->add($newLevel->setPreviousLevel($this->currentLevel));
        $this->currentLevel = $newLevel;

        return $this;
    }

    /**
     * @return StockLevel
     */
    public function getCurrentLevel()
    {
        return $this->currentLevel;
    }

    /**
     * @return Collection|StockLevel[]
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

    public function getVirtualStock()
    {
        return $this->getStock() - $this->getAllocatedStock();
    }

    /**
     * @param string    $trigger        Action that triggered the change
     * @param int|null  $stock          New stock or null if it should remain unchanged
     * @param int|null  $allocatedStock New allocated stock or null if it should remain unchanged
     * @param User|null $user           User who triggered the change, if left null, it is automatically assigned ot
     *                                  current one
     * @param null      $subject        Any entity that should be associated to this operation
     */
    public function setStockLevels($trigger, $stock = null, $allocatedStock = null, User $user = null, $subject = null)
    {
        $this->changeCurrentLevel(new StockLevel(
            $this,
            $stock === null ? $this->getStock() : $stock,
            $allocatedStock === null ? $this->getAllocatedStock() : $allocatedStock,
            $trigger,
            $this->getCurrentLevel(),
            $user,
            $subject
        ));
    }

    /**
     * @param string    $trigger        Action that triggered the change
     * @param int|null  $stock          New stock or null if it should remain unchanged
     * @param int|null  $allocatedStock New allocated stock or null if it should remain unchanged
     * @param User|null $user           User who triggered the change, if left null, it is automatically assigned ot
     *                                  current one
     * @param null      $subject        Any entity that should be associated to this operation
     */
    public function adjustStockLevels(
        $trigger,
        $stock = null,
        $allocatedStock = null,
        User $user = null,
        $subject = null
    ) {
        $this->setStockLevels(
            $trigger,
            $stock === null ? $this->getStock() : ($this->getStock() + $stock),
            $allocatedStock === null ? $this->getAllocatedStock() : ($this->getAllocatedStock() + $allocatedStock),
            $user,
            $subject
        );
    }
}
