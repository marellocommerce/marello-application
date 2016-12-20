<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface InventoryBalancerInterface
{
    public function process();

    public function setInventoryUpdateContext(InventoryUpdateContext $context);

    public function setInventoryManager(InventoryManagerInterface $manager);

    public function setDispatcher(EventDispatcherInterface $dispatcher);
}
