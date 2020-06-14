<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class RemoveRemoteDataForDisabledIntegrationMessage implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';
    public const IS_REMOVED = 'is_removed';
    public const IS_DEACTIVATED = 'is_deactivated';
    public const TRANSPORT_SETTING_BAG = 'transport_setting_bag';
    public const PRODUCT_IDS_WITH_SKU = 'product_ids_with_sku';

    /** @var int */
    protected $integrationId;

    /** @var bool */
    protected $isRemoved;

    /** @var bool */
    protected $isDeactivated;

    /** @var string */
    protected $transportSettingBagSerialized;

    /** @var array */
    protected $productIdsWithSku;

    /**
     * @param int $integrationId
     * @param bool $isRemoved
     * @param bool $isDeactivated
     * @param string $transportSettingBag
     * @param array $productIdsWithSku
     */
    public function __construct(
        int $integrationId,
        bool $isRemoved,
        bool $isDeactivated,
        string $transportSettingBag,
        array $productIdsWithSku
    ) {
        $this->integrationId = $integrationId;
        $this->isRemoved = $isRemoved;
        $this->isDeactivated = $isDeactivated;
        $this->transportSettingBagSerialized = $transportSettingBag;
        $this->productIdsWithSku = $productIdsWithSku;
    }

    /**
     * @return int
     */
    public function getIntegrationId(): int
    {
        return $this->integrationId;
    }

    /**
     * @return bool
     */
    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    /**
     * @return bool
     */
    public function isDeactivated(): bool
    {
        return $this->isDeactivated;
    }

    /**
     * @return string
     */
    public function getTransportSettingBagSerialized(): string
    {
        return $this->transportSettingBagSerialized;
    }

    /**
     * @return array
     */
    public function getProductIdsWithSku(): array
    {
        return $this->productIdsWithSku;
    }

    /**
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            'integration_id' => $this->integrationId,
            'is_removed' => $this->isRemoved,
            'is_deactivated' => $this->isDeactivated
        ];
    }

    /**
     * @param MessageInterface $message
     * @return RemoveRemoteDataForDisabledIntegrationMessage
     */
    public static function createFromMessage(MessageInterface $message): RemoveRemoteDataForDisabledIntegrationMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new RemoveRemoteDataForDisabledIntegrationMessage(
            $messageData[self::INTEGRATION_ID],
            $messageData[self::IS_REMOVED],
            $messageData[self::IS_DEACTIVATED],
            $messageData[self::TRANSPORT_SETTING_BAG],
            $messageData[self::PRODUCT_IDS_WITH_SKU]
        );

        return $message;
    }
}
