<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity
 */
class Magento2Transport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="api_url", type="string", length=255, nullable=false)
     */
    protected $apiUrl;

    /**
     * @todo Encode API Token
     *
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=255, nullable=false)
     */
    protected $apiToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sync_start_date", type="date")
     */
    protected $syncStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initial_sync_start_date", type="datetime", nullable=true)
     */
    protected $initialSyncStartDate;

    /**
     * @var array
     * [
     *    [
     *      'website_code' => string <website_code>,
     *      'sales_chanel_code' => string <sales_channel_code>
     *    ],
     *    ...
     * ]
     *
     * @ORM\Column(name="websites_sales_channel_mapping", type="json", nullable=true)
     */
    protected $websiteToSalesChannelMapping = [];

    /**
     * @var \DateInterval
     *
     * @ORM\Column(name="sync_range", type="string", length=50)
     */
    protected $syncRange;

    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     * @return ParameterBag|void
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new Magento2TransportSettings(
                [
                    'api_url' => $this->getApiUrl(),
                    'api_token' => $this->getApiToken(),
                    'sync_range' => $this->getSyncRange(),
                    'start_sync_date' => $this->getSyncStartDate(),
                    'initial_sync_start_date' => $this->getInitialSyncStartDate(),
                    'website_to_sales_channel_mapping' => $this->getWebsiteToSalesChannelMapping()
                ]
            );
        }

        return $this->settings;
    }

    public function __construct()
    {
        $this->setSyncStartDate(new \DateTime('2007-01-01', new \DateTimeZone('UTC')));
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
     * @return \DateInterval|null
     */
    public function getSyncRange(): ?\DateInterval
    {
        return $this->syncRange;
    }

    /**
     * @param \DateInterval $syncRange
     * @return $this
     */
    public function setSyncRange(\DateInterval $syncRange): self
    {
        $this->syncRange = $syncRange;

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
}
