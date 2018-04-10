<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 03/04/2018
 * Time: 14:56
 */

namespace Marello\Bundle\MageBridgeBundle\Provider\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\MageBridgeBundle\Provider\Transport\RestTransport;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class ProductReader extends \Oro\Bundle\ImportExportBundle\Reader\EntityReader
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var Channel */
    protected $channel;

    /** @var LoggerStrategy */
    protected $logger;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    /** @var bool[] */
    protected $loaded = [];

    /** @var array */
    protected $ids = [];

    /**
     * Flag to control if read entity is used by the bridge extension
     *
     * @var bool
     */
    protected $extensionUsed = true;

    /**
     * @param ContextRegistry $contextRegistry
     * @param LoggerStrategy $logger
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        ManagerRegistry $registry,
        OwnershipMetadataProviderInterface $ownershipMetadata,
        RestTransport $transport,
        LoggerStrategy $logger
    ) {
        parent::__construct($contextRegistry, $registry, $ownershipMetadata);

        $this->logger = $logger;
        $this->transport = $transport;

        $this->logger->info("xxxxxx");
    }

    /**
     * @param RestTransport $transport
     */
    public function setTransport(RestTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        //TODO: filter product based on sales channel related to the magento intergration

        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);
        return $qb;
    }
}