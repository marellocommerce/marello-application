<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Async\RemoveRemoteDataForDisabledIntegrationMessage as Message;

class RemoteDataRemovingDTO
{
    public const STATUS_REMOVED = 'removed';
    public const STATUS_DEACTIVATED = 'deactivated';

    /** @var int */
    protected $integrationId;

    /** @var string */
    protected $status;

    /** @var Magento2TransportSettings */
    protected $transportSettings;

    /** @var array */
    protected $productIdWithSkuToRemove;

    /**
     * @param int $integrationId
     * @param string $status
     * @param Magento2TransportSettings $transportSettings
     * @param array $productIdWithSkuToRemove
     */
    public function __construct(
        int $integrationId,
        string $status,
        Magento2TransportSettings $transportSettings,
        array $productIdWithSkuToRemove = []
    ) {
        if (!\in_array($status, [self::STATUS_REMOVED, self::STATUS_DEACTIVATED], true)) {
            throw new RuntimeException(
                sprintf('Status must be "removed" or "deactivated", but "%s" given !', $status)
            );
        }

        $this->integrationId = $integrationId;
        $this->status = $status;
        $this->transportSettings = $transportSettings;
        $this->productIdWithSkuToRemove = $productIdWithSkuToRemove;
    }

    /**
     * @return bool
     */
    public function isRemovedIntegration(): bool
    {
        return $this->status === self::STATUS_DEACTIVATED;
    }

    /**
     * @return bool
     */
    public function hasProductsOnRemoteRemove(): bool
    {
        return !empty($this->productIdWithSkuToRemove);
    }

    /**
     * @return array
     */
    public function getMessageBody(): array
    {
        return [
            Message::INTEGRATION_ID => $this->integrationId,
            Message::IS_REMOVED => $this->status === self::STATUS_REMOVED,
            Message::IS_DEACTIVATED => $this->status === self::STATUS_DEACTIVATED,
            Message::TRANSPORT_SETTING_BAG => \serialize($this->transportSettings->all()),
            Message::PRODUCT_IDS_WITH_SKU => $this->productIdWithSkuToRemove,
        ];
    }
}
