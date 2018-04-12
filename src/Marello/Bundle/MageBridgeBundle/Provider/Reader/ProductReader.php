<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 03/04/2018
 * Time: 14:56
 */

namespace Marello\Bundle\MageBridgeBundle\Provider\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Marello\Bundle\MageBridgeBundle\Provider\Transport\RestTransport;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class ProductReader extends \Oro\Bundle\ImportExportBundle\Reader\EntityReader
{
    const MAGENTO_REST_TYPE = 'magento';

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
     * @param RestTransport $transport
     * @return $this
     */
    public function setTransport(RestTransport $transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @param LoggerStrategy $logger
     * @return $this
     */
    public function setLogger(LoggerStrategy $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $queryBuilder = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $queryBuilder->where($queryBuilder->expr()->in("o.type", ":type"))
            ->setParameter('type', self::MAGENTO_REST_TYPE);

        return $queryBuilder;
    }


    /**
     * @param $entityName
     * @param $salesChannels
     */
    public function setProductSourceEntityName($entityName, $salesChannels)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry
            ->getManagerForClass($entityName);

        $queryBuilder = $entityManager
            ->getRepository($entityName)
            ->createQueryBuilder('product');

        $queryBuilder
            ->where(
                $queryBuilder->expr()->isMemberOf(':salesChannel', 'product.channels')
            )
            ->setParameter('salesChannel', $salesChannels);

        $this->setSourceQuery($this->applyAcl($queryBuilder));
    }


    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $entity = parent::read();

        $salesChannels = $entity->getTransport()->getSalesChannels();

        $this->setProductSourceEntityName(Product::class, $salesChannels);

        return parent::read();
    }

}