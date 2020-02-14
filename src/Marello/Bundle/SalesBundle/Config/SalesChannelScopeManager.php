<?php

namespace Marello\Bundle\SalesBundle\Config;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;

class SalesChannelScopeManager extends AbstractScopeManager
{
    const SCOPE_NAME = 'saleschannel';

    protected $scopeId = 0;

    public function getScopedEntityName()
    {
        return self::SCOPE_NAME;
    }

    public function setScopeId($scopeId)
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId;
    }

    public function getScopeId()
    {
        return $this->scopeId;
    }

    protected function isSupportedScopeEntity($entity)
    {
        return $entity instanceof SalesChannel;
    }

    protected function getScopeEntityIdValue($entity)
    {
        return $entity->getId();
    }
}
