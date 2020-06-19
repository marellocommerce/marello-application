<?php

namespace Marello\Bundle\Magento2Bundle\Stack;

use Marello\Bundle\Magento2Bundle\DTO\ProductChangesByChannelDTO;

class ProductChangesByChannelStack
{
    /** @var ProductChangesByChannelDTO[] */
    protected $changesByChannel = [];

    /**
     * @param int $integrationChannelId
     * @return ProductChangesByChannelDTO
     */
    public function getOrCreateChangesDtoByChannelId(int $integrationChannelId): ProductChangesByChannelDTO
    {
        if (!array_key_exists($integrationChannelId, $this->changesByChannel)) {
            $this->changesByChannel[$integrationChannelId] = new ProductChangesByChannelDTO($integrationChannelId);
        }

        return $this->changesByChannel[$integrationChannelId];
    }

    /**
     * @param int $integrationChannelId
     */
    public function clearChangesByChannelId(int $integrationChannelId): void
    {
        unset($this->changesByChannel[$integrationChannelId]);
    }

    /**
     * @return ProductChangesByChannelDTO[]
     */
    public function getChangesDTOs(): array
    {
        return $this->changesByChannel;
    }

    public function clearStack(): void
    {
        $this->changesByChannel = [];
    }
}
