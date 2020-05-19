<?php

namespace Marello\Bundle\Magento2Bundle\Handler;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelsProvider;
use Marello\Bundle\Magento2Bundle\Provider\WebsitesProvider;
use Marello\Bundle\Magento2Bundle\Transport\Magento2TransportInterface;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Handler has method getCheckResponse which allows to check configuration of the integration
 */
class TransportHandler
{
    /**
     * @var TypesRegistry
     */
    protected $typesRegistry;

    /**
     * @var TransportEntityHandler
     */
    protected $transportEntityHandler;

    /**
     * @var WebsitesProvider
     */
    protected $websitesProvider;

    /**
     * @var SalesChannelsProvider
     */
    protected $salesChannelsProvider;

    /**
     * @param TypesRegistry $typesRegistry
     * @param TransportEntityHandler $transportEntityHandler
     * @param WebsitesProvider $websitesProvider
     * @param SalesChannelsProvider $salesChannelsProvider
     */
    public function __construct(
        TypesRegistry $typesRegistry,
        TransportEntityHandler $transportEntityHandler,
        WebsitesProvider $websitesProvider,
        SalesChannelsProvider $salesChannelsProvider
    ) {
        $this->typesRegistry = $typesRegistry;
        $this->transportEntityHandler = $transportEntityHandler;
        $this->websitesProvider = $websitesProvider;
        $this->salesChannelsProvider = $salesChannelsProvider;
    }

    /**
     * @param Request $request
     * @param string $integrationType
     * @param string $transportType
     * @param Magento2Transport $transportEntity
     * @return array
     */
    public function getCheckResponse(
        Request $request,
        string $integrationType,
        string $transportType,
        Magento2Transport $transportEntity
    ): array {
        $transport = $this->getMagentoTransport($integrationType, $transportType);
        $transportEntity = $this->transportEntityHandler->getHandledTransportEntity(
            $request,
            $transport,
            $transportEntity
        );

        $transport->initWithExtraOptions(
            $transportEntity,
            MultiAttemptsConfigTrait::getMultiAttemptsDisabledConfig()
        );

        return  [
            'success' => true,
            'websites' => $this->websitesProvider->getFormattedWebsites($transport),
            'salesChannels' => $this->salesChannelsProvider->getFormattedSalesChannels()
        ];
    }

    /**
     * @param string $integrationType
     * @param string $transportType
     * @return Magento2TransportInterface
     */
    protected function getMagentoTransport(string $integrationType, string $transportType): Magento2TransportInterface
    {
        /** @var Magento2TransportInterface $transport */
        $transport = $this->typesRegistry->getTransportType($integrationType, $transportType);

        if (!$transport instanceof Magento2TransportInterface) {
            throw new UnexpectedTypeException($transport, Magento2TransportInterface::class);
        }

        return $transport;
    }
}
