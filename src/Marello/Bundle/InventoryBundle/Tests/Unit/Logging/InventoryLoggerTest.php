<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Logging\InventoryLogger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class InventoryLoggerTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $uow;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $manager;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $doctrine;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    protected $storage;

    /** @var InventoryLogger */
    protected $logger;

    /**
     * Set up test.
     */
    public function setUp()
    {
        $this->uow      = $this->prophesize(UnitOfWork::class);
        $this->manager  = $this->prophesize(EntityManager::class);
        $this->doctrine = $this->prophesize(Registry::class);
        $this->storage  = $this->prophesize(TokenStorageInterface::class);

        /*
         * Do nothing when asked to compute change sets...
         */
        $this->uow
            ->computeChangeSets()
            ->willReturn(null);

        /*
         * Registry should return manager...
         */
        $this->doctrine
            ->getManager()
            ->willReturn($this->manager->reveal());

        /*
         * Manager should return unit of work...
         */
        $this->manager
            ->getUnitOfWork()
            ->willReturn($this->uow->reveal());

        /*
         * Create logger...
         */
        $this->logger = new InventoryLogger($this->doctrine->reveal(), $this->storage->reveal());
    }

    /**
     * @test
     * @covers InventoryLogger::directLog
     */
    public function directLogCreatesLogWhenLoggingChange()
    {
        /**
         * Mock entity manager...
         */
        $this->manager
            ->persist(Argument::type(InventoryLog::class))
            ->shouldBeCalled();

        /*
         * Call tested method...
         */
        $this->logger->directLog(new InventoryItem(), 'test', function (InventoryLog $log) {
            $log->setNewQuantity(10); // Set new quantity to 10 (log increase of 10).
        });
    }

    /**
     * @test
     * @covers InventoryLogger::directLog
     */
    public function directLogDoesNotCreateLogWhenThereIsNoChange()
    {
        /**
         * Mock entity manager...
         */
        $this->manager
            ->persist()
            ->shouldNotBeCalled();

        /*
         * Call tested method...
         */
        $this->logger->directLog(new InventoryItem(), 'test', function (InventoryLog $log) {
            // No change ...
        });
    }

    /**
     * @test
     * @covers InventoryLogger::log
     */
    public function logCreatesLogForNewItem()
    {
        $inventoryItem = (new InventoryItem())
            ->setQuantity(10)
            ->setAllocatedQuantity(20);

        /*
         * Act as if entity was new...
         */
        $this->uow
            ->getEntityState(Argument::any(), Argument::any())
            ->willReturn(UnitOfWork::STATE_NEW);

        /*
         * Check if log values correspond to what's expected...
         */
        $this->manager
            ->persist(Argument::that(function ($log) {
                $this->assertInstanceOf(InventoryLog::class, $log);
                $this->assertEquals(0, $log->getOldQuantity(), 'Old quantity for new item should be 0.');
                $this->assertEquals(0, $log->getOldAllocatedQuantity(), 'Old allocated quantity should be 0.');
                $this->assertEquals(10, $log->getNewQuantity(), 'New quantity should be 10 (as specified change).');
                $this->assertEquals(
                    20,
                    $log->getNewAllocatedQuantity(),
                    'New allocated quantity should be 20 (as specified change).'
                );
                $this->assertEquals('test', $log->getActionType(), 'Log trigger should be "test".');

                return true;
            }))
            ->shouldBeCalled();

        $this->logger->log($inventoryItem, 'test');
    }

    /**
     * @test
     * @covers InventoryLogger::log
     */
    public function logDoesNotCreateLogForUnchangedNewItems()
    {
        /*
         * Unchanged inventory item...
         */
        $inventoryItem = (new InventoryItem());

        /*
         * Act as if item was new.
         */
        $this->uow
            ->getEntityState(Argument::any(), Argument::any())
            ->willReturn(UnitOfWork::STATE_NEW);

        /*
         * New log is not persisted.
         */
        $this->manager
            ->persist()
            ->shouldNotBeCalled();

        $this->logger->log($inventoryItem, 'test');
    }

    /**
     * Provides change sets for when log is created for managed entities.
     *
     * @return array
     */
    public function changeSetDataProvider()
    {
        return [
            'nothing_changed'            => [[]],
            'quantity_changed'           => [['quantity' => [5, 10]]],
            'allocated_quantity_changed' => [['allocatedQuantity' => [10, 20]]],
            'both_quantities_changed'    => [['quantity' => [5, 10], 'allocatedQuantity' => [10, 20]]],
        ];
    }

    /**
     * @test
     * @covers       InventoryLogger::log
     * @dataProvider changeSetDataProvider
     *
     * @param array $changeSet
     */
    public function testLogForModifiedItem($changeSet)
    {
        $oldQuantity  = array_key_exists('quantity', $changeSet) ? $changeSet['quantity'][0] : 10;
        $newQuantity  = array_key_exists('quantity', $changeSet) ? $changeSet['quantity'][1] : 10;
        $oldAllocated = array_key_exists('allocatedQuantity', $changeSet) ? $changeSet['allocatedQuantity'][0] : 20;
        $newAllocated = array_key_exists('allocatedQuantity', $changeSet) ? $changeSet['allocatedQuantity'][1] : 20;

        $inventoryItem = (new InventoryItem())
            ->setQuantity($newQuantity)
            ->setAllocatedQuantity($newAllocated);

        /*
         * Act as if entity was managed...
         */
        $this->uow
            ->getEntityState(Argument::any(), Argument::any())
            ->willReturn(UnitOfWork::STATE_MANAGED);

        /*
         * Lets return fake change set.
         */
        $this->uow
            ->getEntityChangeSet(Argument::exact($inventoryItem))
            ->willReturn($changeSet);

        if (!empty($changeSet)) {
            /*
             * If there is something in the change set, expect a change to be logged.
             */
            $this->manager
                ->persist(Argument::that(function ($log) use (
                    $oldQuantity,
                    $newQuantity,
                    $oldAllocated,
                    $newAllocated
                ) {
                    $this->assertInstanceOf(InventoryLog::class, $log);
                    $this->assertEquals(
                        $oldQuantity,
                        $log->getOldQuantity(),
                        "Old quantity for new item should be $oldQuantity."
                    );
                    $this->assertEquals(
                        $oldAllocated,
                        $log->getOldAllocatedQuantity(),
                        "Old allocated quantity should be $oldAllocated."
                    );
                    $this->assertEquals(
                        $newQuantity,
                        $log->getNewQuantity(),
                        "New quantity should be $newQuantity."
                    );
                    $this->assertEquals(
                        $newAllocated,
                        $log->getNewAllocatedQuantity(),
                        "New allocated quantity should be $newAllocated."
                    );
                    $this->assertEquals('test', $log->getActionType(), 'Log trigger should be "test".');

                    return true;
                }))
                ->shouldBeCalled();
        } else {
            /*
             * If there is nothing in the change set, expect nothing to be logged.
             */
            $this->manager
                ->persist(Argument::type(InventoryLog::class))
                ->shouldNotBeCalled();
        }

        $this->logger->log($inventoryItem, 'test');
    }
}
