<?php

namespace Marello\Bundle\OroCommerceBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Psr\Log\LoggerInterface;

class SyncState
{
    const LAST_SYNC_DATE_KEY = 'lastSyncDate';

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
    }

    /**
     * @param Channel $channel
     * @param string  $connector
     *
     * @return \DateTime|null
     */
    public function getLastSyncDate(Channel $channel, $connector)
    {
        /**
         * @var $channelRepository ChannelRepository
         */
        $channelRepository = $this->managerRegistry->getRepository('OroIntegrationBundle:Channel');
        $status = $channelRepository->getLastStatusForConnector($channel, $connector, Status::STATUS_COMPLETED);

        if (null === $status) {
            return null;
        }

        $date = null;
        $statusData = $status->getData();
        if (!empty($statusData[self::LAST_SYNC_DATE_KEY])) {
            try {
                $date = new \DateTime($statusData[self::LAST_SYNC_DATE_KEY], new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                $this->getLogger()->error(
                    sprintf(
                        'Status with [id=%s] contains incorrect date format in data by key "lastSyncDate".',
                        $status->getId()
                    ),
                    ['exception' => $e]
                );
            }
        }

        return $date;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
}
