<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class ClearInternalDataForDisabledIntegrationMessage implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';

    /** @var int */
    protected $integrationId;

    /**
     * @param int $integrationId
     */
    public function __construct(int $integrationId)
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @return int
     */
    public function getIntegrationId(): int
    {
        return $this->integrationId;
    }

    /**
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            'integration_id' => $this->integrationId
        ];
    }

    /**
     * @param MessageInterface $message
     * @return ClearInternalDataForDisabledIntegrationMessage
     */
    public static function createFromMessage(MessageInterface $message): ClearInternalDataForDisabledIntegrationMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new ClearInternalDataForDisabledIntegrationMessage(
            $messageData[self::INTEGRATION_ID]
        );

        return $message;
    }
}
