<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

class OrderIdentifierDTO
{
    /** @var int */
    private $marelloOrderId;

    /** @var int */
    private $magentoOrderId;

    /**
     * @param int $marelloOrderId
     * @param int $magentoOrderId
     */
    public function __construct(int $marelloOrderId, int $magentoOrderId)
    {
        $this->marelloOrderId = $marelloOrderId;
        $this->magentoOrderId = $magentoOrderId;
    }

    /**
     * @return int
     */
    public function getMarelloOrderId(): int
    {
        return $this->marelloOrderId;
    }

    /**
     * @return int
     */
    public function getMagentoOrderId(): int
    {
        return $this->magentoOrderId;
    }
}
