<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Oro\Bundle\ActionBundle\Model\ActionGroupRegistry;

/**
 * Class used to act as public access service to get an action from the action group registry
 * this is used as temporary service in order to maintain BC while keeping functionality
 * Class ActionGroupRegistryProvider
 * @package Marello\Bundle\ProductBundle\Provider
 */
class ActionGroupRegistryProvider
{
    /**
     * @param ActionGroupRegistry $registry
     */
    public function __construct(ActionGroupRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function findActionByName(string $actionName)
    {
        return $this->registry->findByName($actionName);
    }
}
