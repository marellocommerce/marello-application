<?php

namespace Marello\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class MagentoRestTransport
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository")
 */
class MagentoRestTransport extends MagentoTransport
{
    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=255, nullable=false)
     */
    protected $apiToken;

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    'api_user' => $this->getApiUser(),
                    'api_key' => $this->getApiKey(),
                    'api_url' => $this->getApiUrl(),
                    'sync_range' => $this->getSyncRange(),
                    'website_id' => $this->getWebsiteId(),
                    'start_sync_date' => $this->getSyncStartDate(),
                    'initial_sync_start_date' => $this->getInitialSyncStartDate(),
                    'extension_version' => $this->getExtensionVersion(),
                    'magento_version' => $this->getMagentoVersion(),
                    'api_token' => $this->getApiToken(),
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @param string $apiToken
     *
     * @return MagentoTransport
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        /**
         * We can update api_token several times while getting some data from instance
         * that's why we should reset settings object to have fresh new settings
         */
        $this->settings = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedOrderNoteExtensionVersion()
    {
        return false;
    }
}
