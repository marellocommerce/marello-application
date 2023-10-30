<?php

namespace Marello\Bundle\SalesBundle\Config;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;

class SalesChannelScopeManager extends AbstractScopeManager
{
    const SCOPE_NAME = 'saleschannel';

    protected $scopeId = 0;

    public function getScopedEntityName(): string
    {
        return self::SCOPE_NAME;
    }

    public function setScopeId(int $scopeId): void
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId;
    }

    public function getScopeId(): ?int
    {
        return $this->scopeId;
    }

    protected function isSupportedScopeEntity(object $entity): bool
    {
        return $entity instanceof SalesChannel;
    }

    protected function getScopeEntityIdValue(object $entity): mixed
    {
        return $entity->getId();
    }
}
