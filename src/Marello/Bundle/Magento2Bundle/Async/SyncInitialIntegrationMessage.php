<?php

namespace Marello\Bundle\Magento2Bundle\Async;

use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SyncInitialIntegrationMessage implements IntegrationAwareMessageInterface
{
    public const INTEGRATION_ID = 'integration_id';
    public const CONNECTOR_PARAMETERS = 'connector_parameters';

    /** @var int */
    protected $integrationId;

    /** @var array */
    protected $connectorParameters;

    /**
     * @param int $integrationId
     * @param array $connectorParameters
     */
    public function __construct(int $integrationId, array $connectorParameters)
    {
        $this->integrationId = $integrationId;
        $this->connectorParameters = $connectorParameters;
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
    public function getConnectorParameters(): array
    {
        return $this->connectorParameters;
    }

    /**
     * @return array
     */
    public function getContextParams(): array
    {
        return [
            self::INTEGRATION_ID => $this->integrationId,
            self::CONNECTOR_PARAMETERS => $this->connectorParameters
        ];
    }

    /**
     * @param MessageInterface $message
     * @return SyncInitialIntegrationMessage
     */
    public static function createFromMessage(MessageInterface $message): SyncInitialIntegrationMessage
    {
        $messageData = JSON::decode($message->getBody());

        $message = new SyncInitialIntegrationMessage(
            $messageData[self::INTEGRATION_ID],
            $messageData[self::CONNECTOR_PARAMETERS] ?? []
        );

        return $message;
    }
}
