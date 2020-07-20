<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * @ORM\Entity
 */
class Magento2Transport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="m2_api_url", type="string", length=255, nullable=true)
     */
    protected $apiUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="m2_api_token", type="string", length=255, nullable=true)
     */
    protected $apiToken;

    /**
     * Date time that we use as starting point for end date for initial sync and start date for regular sync
     *
     * @var \DateTime
     *
     * @ORM\Column(name="m2_sync_start_date", type="datetime", nullable=true)
     */
    protected $syncStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="m2_initial_sync_start_date", type="date", nullable=true)
     */
    protected $initialSyncStartDate;

    /**
     * @var array
     *
     * The structure described in constant REQUIRED_KEYS of @see WebsiteToSalesChannelMappingItemDTO
     *
     * @ORM\Column(name="m2_websites_sales_channel_map", type="json", nullable=true)
     */
    protected $websiteToSalesChannelMapping = [];

    /**
     * @var bool
     *
     * @ORM\Column(name="m2_del_remote_data_on_deact", type="boolean", nullable=true)
     */
    protected $deleteRemoteDataOnDeactivation = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="m2_del_remote_data_on_del", type="boolean", nullable=true)
     */
    protected $deleteRemoteDataOnDeletion = false;

    /**
     * @var Magento2TransportSettings
     */
    protected $settings;

    /**
     * @return Magento2TransportSettings
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new Magento2TransportSettings(
                [
                    Magento2TransportSettings::API_URL_KEY => $this->getApiUrl(),
                    Magento2TransportSettings::API_TOKEN_KEY => $this->getApiToken(),
                    Magento2TransportSettings::SYNC_START_DATE_KEY => $this->getSyncStartDate(),
                    Magento2TransportSettings::INITIAL_SYNC_START_DATE_KEY => $this->getInitialSyncStartDate(),
                    Magento2TransportSettings::WEBSITE_TO_SALES_CHANNEL_MAPPING_KEY =>
                        $this->getWebsiteToSalesChannelMapping(),
                    Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION_KEY =>
                        $this->isDeleteRemoteDataOnDeactivation(),
                    Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DELETION_KEY =>
                        $this->isDeleteRemoteDataOnDeletion()
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @return string|null
     */
    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     * @return $this
     */
    public function setApiUrl(string $apiUrl): self
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     * @return $this
     */
    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSyncStartDate(): ?\DateTime
    {
        return $this->syncStartDate;
    }

    /**
     * @param \DateTime $syncStartDate
     * @return $this
     */
    public function setSyncStartDate(\DateTime $syncStartDate): self
    {
        $this->syncStartDate = $syncStartDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getInitialSyncStartDate(): ?\DateTime
    {
        return $this->initialSyncStartDate;
    }

    /**
     * @param \DateTime $initialSyncStartDate
     * @return $this
     */
    public function setInitialSyncStartDate(\DateTime $initialSyncStartDate): self
    {
        $this->initialSyncStartDate = $initialSyncStartDate;

        return $this;
    }

    /**
     * @return array
     */
    public function getWebsiteToSalesChannelMapping(): array
    {
        return $this->websiteToSalesChannelMapping;
    }

    /**
     * @param array $websiteToSalesChannelMapping
     * @return $this
     */
    public function setWebsiteToSalesChannelMapping(array $websiteToSalesChannelMapping): self
    {
        $this->websiteToSalesChannelMapping = $websiteToSalesChannelMapping;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeactivation(): bool
    {
        return $this->deleteRemoteDataOnDeactivation;
    }

    /**
     * @param bool $deleteRemoteDataOnDeactivation
     * @return $this
     */
    public function setDeleteRemoteDataOnDeactivation(bool $deleteRemoteDataOnDeactivation): self
    {
        $this->deleteRemoteDataOnDeactivation = $deleteRemoteDataOnDeactivation;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeletion(): bool
    {
        return $this->deleteRemoteDataOnDeletion;
    }

    /**
     * @param bool $deleteRemoteDataOnDeletion
     * @return $this
     */
    public function setDeleteRemoteDataOnDeletion(bool $deleteRemoteDataOnDeletion): self
    {
        $this->deleteRemoteDataOnDeletion = $deleteRemoteDataOnDeletion;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamp()
    {
        $this->syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
