<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

class ProductIdentifierDTO
{
    /** @var int */
    private $marelloProductId;

    /** @var int */
    private $magentoProductId;

    /**
     * @param int $marelloProductId
     * @param int $magentoProductId
     */
    public function __construct(int $marelloProductId, int $magentoProductId)
    {
        $this->marelloProductId = $marelloProductId;
        $this->magentoProductId = $magentoProductId;
    }

    /**
     * @return int
     */
    public function getMarelloProductId(): int
    {
        return $this->marelloProductId;
    }

    /**
     * @return int
     */
    public function getMagentoProductId(): int
    {
        return $this->magentoProductId;
    }
}
