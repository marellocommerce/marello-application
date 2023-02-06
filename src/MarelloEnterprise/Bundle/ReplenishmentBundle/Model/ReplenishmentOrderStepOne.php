<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Model;

class ReplenishmentOrderStepOne
{
    public const AUTOMATED_TYPE = 'automated';
    public const MANUAL_TYPE = 'manual';

    /**
     * @var string
     */
    protected $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
