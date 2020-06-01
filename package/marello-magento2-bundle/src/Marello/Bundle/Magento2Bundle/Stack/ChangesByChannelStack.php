<?php

namespace Marello\Bundle\Magento2Bundle\Stack;

use Marello\Bundle\Magento2Bundle\DTO\ChangesByChannelDTO;

class ChangesByChannelStack
{
    /** @var ChangesByChannelDTO[] */
    protected $changesByChannel = [];

    /**
     * @param int $integrationChannelId
     * @return ChangesByChannelDTO
     */
    public function getOrCreateChangesDtoByChannelId(int $integrationChannelId): ChangesByChannelDTO
    {
        if (!array_key_exists($integrationChannelId, $this->changesByChannel)) {
            $this->changesByChannel[$integrationChannelId] = new ChangesByChannelDTO($integrationChannelId);
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
     * @return ChangesByChannelDTO[]
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
