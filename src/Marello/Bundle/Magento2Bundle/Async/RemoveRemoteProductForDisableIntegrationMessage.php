<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class RemoveRemoteProductForDisableIntegrationMessage implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';
    public const IS_REMOVED = 'is_removed';
    public const IS_DEACTIVATED = 'is_deactivated';
    public const TRANSPORT_SETTING_BAG = 'transport_setting_bag';
    public const PRODUCT_ID = 'product_id';
    public const PRODUCT_SKU = 'product_sku';
    public const JOB_ID = 'jobId';

    /** @var int */
    protected $integrationId;

    /** @var bool */
    protected $isRemoved;

    /** @var bool */
    protected $isDeactivated;

    /** @var string */
    protected $transportSettingBag;

    /** @var int */
    protected $productId;

    /** @var string */
    protected $productSku;

    /** @var int */
    protected $jobId;

    /**
     * @param int $integrationId
     * @param bool $isRemoved
     * @param bool $isDeactivated
     * @param Magento2TransportSettings $transportSettingBag
     * @param int $productId
     * @param string $productSku
     * @param int $jobId
     */
    public function __construct(
        int $integrationId,
        bool $isRemoved,
        bool $isDeactivated,
        Magento2TransportSettings $transportSettingBag,
        int $productId,
        string $productSku,
        int $jobId
    ) {
        $this->integrationId = $integrationId;
        $this->isRemoved = $isRemoved;
        $this->isDeactivated = $isDeactivated;
        $this->transportSettingBag = $transportSettingBag;
        $this->productId = $productId;
        $this->productSku = $productSku;
        $this->jobId = $jobId;
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
     * @return Magento2TransportSettings
     */
    public function getTransportSettingBag(): Magento2TransportSettings
    {
        return $this->transportSettingBag;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->productSku;
    }

    /**
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            'integration_id' => $this->integrationId,
            'is_removed' => $this->isRemoved,
            'is_deactivated' => $this->isDeactivated,
            'product_id' => $this->productId,
            'product_sku' => $this->productSku
        ];
    }

    /**
     * @param MessageInterface $message
     * @return RemoveRemoteProductForDisableIntegrationMessage
     */
    public static function createFromMessage(MessageInterface $message): RemoveRemoteProductForDisableIntegrationMessage
    {
        $messageData = JSON::decode($message->getBody());

        $transportSettingBagData = \unserialize(
            $messageData[self::TRANSPORT_SETTING_BAG],
            [
                '\DateInterval',
                '\DateTime',
            ]
        );
        $transportSettingBag = new Magento2TransportSettings($transportSettingBagData);

        $message = new RemoveRemoteProductForDisableIntegrationMessage(
            $messageData[self::INTEGRATION_ID],
            $messageData[self::IS_REMOVED],
            $messageData[self::IS_DEACTIVATED],
            $transportSettingBag,
            $messageData[self::PRODUCT_ID],
            $messageData[self::PRODUCT_SKU],
            $messageData[self::JOB_ID]
        );

        return $message;
    }
}
