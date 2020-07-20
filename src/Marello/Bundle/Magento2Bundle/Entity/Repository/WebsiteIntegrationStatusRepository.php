<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\Magento2Bundle\Entity\WebsiteIntegrationStatus;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class WebsiteIntegrationStatusRepository extends EntityRepository
{
    /** @var ChannelRepository */
    protected $channelRepository;

    /**
     * @param ChannelRepository $channelRepository
     */
    public function setChannelRepository(ChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @param int $websiteId
     * @param string|null $code
     *
     * @return WebsiteIntegrationStatus|null
     * @throws NonUniqueResultException
     */
    public function getLastWebsiteStatusForConnector(
        Integration $integration,
        string $connector,
        int $websiteId,
        string $code = null
    ): ?WebsiteIntegrationStatus {
        $queryBuilder = $this->getConnectorWebsiteStatusesQueryBuilder($integration, $connector, $websiteId, $code);
        $queryBuilder
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @param int $websiteId
     * @param string|null $code
     *
     * @return QueryBuilder
     */
    public function getConnectorWebsiteStatusesQueryBuilder(
        Integration $integration,
        string $connector,
        int $websiteId,
        string $code = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('m2status_p_website');
        $qb->select('m2status_p_website')
            ->innerJoin('m2status_p_website.status', 'status')
            ->where($qb->expr()->eq('status.channel', ':integration'))
            ->andWhere($qb->expr()->eq('status.connector', ':connector'))
            ->andWhere($qb->expr()->eq('IDENTITY(m2status_p_website.website)', ':websiteId'))
            ->setParameters(['integration' => $integration, 'connector' => $connector, 'websiteId' => $websiteId])
            ->orderBy('status.date', Criteria::DESC);

        if ($code) {
            $qb
                ->andWhere('status.code = :code')
                ->setParameter('code', $code);
        }

        return $qb;
    }

    /**
     * Adds status to integration, manual persist of newly created statuses and do flush.
     *
     * @param Integration $integration
     * @param WebsiteIntegrationStatus $status
     */
    public function addStatusAndFlush(Integration $integration, WebsiteIntegrationStatus $status)
    {
        if ($this->getEntityManager()->isOpen()) {
            $integration = $this->channelRepository->getOrLoadById($integration->getId());

            $this->getEntityManager()->persist($status);
            $integration->addStatus($status->getInnerStatus());

            $this->getEntityManager()->flush();
        }
    }
}
