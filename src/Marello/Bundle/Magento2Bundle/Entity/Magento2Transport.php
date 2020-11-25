<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Model\WebsiteToSalesChannelMapItem;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
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
     * @var null|ArrayCollection
     */
    protected $websitesToSalesChannelMapItems;

    /**
     * @var array
     *
     * @ORM\Column(name="m2_websites_sales_channel_map", type="json", nullable=true)
     */
    protected $websitesToSalesChannelMap = [];

    /**
     * @var bool
     *
     * @ORM\Column(name="m2_del_remote_data_on_deact", type="boolean", nullable=true)
     */
    protected $deleteRemoteDataOnDeactivation = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="m2_del_remote_prod_webs_only", type="boolean", nullable=true)
     */
    protected $deleteRemoteProductFromWebsiteOnly = false;

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
                    Magento2TransportSettings::WEBSITE_TO_SALES_CHANNEL_MAP_ITEMS_KEY =>
                        $this->getWebsitesToSalesChannelMapItems(),
                    Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION_KEY =>
                        $this->isDeleteRemoteDataOnDeactivation(),
                    Magento2TransportSettings::DELETE_REMOTE_DATA_ON_DELETION_KEY =>
                        $this->isDeleteRemoteDataOnDeletion(),
                    Magento2TransportSettings::DELETE_REMOTE_PRODUCT_FROM_WEBSITE_ONLY =>
                        $this->isDeleteRemoteProductFromWebsiteOnly()
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
     * @return ArrayCollection
     */
    public function getWebsitesToSalesChannelMapItems(): ArrayCollection
    {
        if (null === $this->websitesToSalesChannelMapItems) {
            $mapItemsArray = \array_map(
                WebsiteToSalesChannelMapItem::createFromCallable(),
                $this->websitesToSalesChannelMap
            );
            $this->websitesToSalesChannelMapItems = new ArrayCollection($mapItemsArray);
        }

        return $this->websitesToSalesChannelMapItems;
    }

    /**
     * @param ArrayCollection $websitesToSalesChannelMapItems
     * @return $this
     */
    public function setWebsitesToSalesChannelMapItems(ArrayCollection $websitesToSalesChannelMapItems): self
    {
        $this->websitesToSalesChannelMapItems = $websitesToSalesChannelMapItems;
        $this->updateWebsiteToSalesChannelMap();

        return $this;
    }

    /**
     * @param WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem
     * @return $this
     */
    public function addWebsiteToSalesChannelMapItem(WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem): self
    {
        $this->getWebsitesToSalesChannelMapItems()->add($websiteToSalesChannelMapItem);
        $this->updateWebsiteToSalesChannelMap();

        return $this;
    }

    /**
     * @param WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem
     * @return $this
     */
    public function removeWebsiteToSalesChannelMapItem(WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem): self
    {
        $this->getWebsitesToSalesChannelMapItems()->removeElement($websiteToSalesChannelMapItem);
        $this->updateWebsiteToSalesChannelMap();

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeactivation(): ?bool
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
    public function isDeleteRemoteDataOnDeletion(): ?bool
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
     * @param bool $deleteRemoteProductFromWebsiteOnly
     * @return $this
     */
    public function setDeleteRemoteProductFromWebsiteOnly(bool $deleteRemoteProductFromWebsiteOnly): self
    {
        $this->deleteRemoteProductFromWebsiteOnly = $deleteRemoteProductFromWebsiteOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteProductFromWebsiteOnly(): ?bool
    {
        return $this->deleteRemoteProductFromWebsiteOnly;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->syncStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updateWebsiteToSalesChannelMap();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updateWebsiteToSalesChannelMap();
    }

    protected function updateWebsiteToSalesChannelMap(): void
    {
        $this->websitesToSalesChannelMap = $this->websitesToSalesChannelMapItems->map(
            function (WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem) {
                return $websiteToSalesChannelMapItem->toArray();
            }
        )->toArray();
    }
}
