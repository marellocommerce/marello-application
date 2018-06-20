<?php

namespace Marello\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class MagentoSoapTransport
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository")
 */
class MagentoSoapTransport extends MagentoTransport
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_wsi_mode", type="boolean")
     */
    protected $isWsiMode = false;

    /**
     * @var string
     */
    protected $wsdlCachePath;

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
                    'sync_range' => $this->getSyncRange(),
                    'website_id' => $this->getWebsiteId(),
                    'start_sync_date' => $this->getSyncStartDate(),
                    'initial_sync_start_date' => $this->getInitialSyncStartDate(),
                    'extension_version' => $this->getExtensionVersion(),
                    'magento_version' => $this->getMagentoVersion(),
                    'api_url' => $this->getWsdlPath(),
                    'wsdl_url' => $this->getWsdlPath(),
                    'wsi_mode' => $this->getIsWsiMode(),
                    'currency' => $this->getCurrency(),
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @return string
     */
    protected function getWsdlPath()
    {
        if ($this->wsdlCachePath) {
            return $this->wsdlCachePath;
        }

        return $this->getApiUrl();
    }

    /**
     * @param string $wsdlCachePath
     *
     * @return MagentoTransport
     */
    public function setWsdlCachePath($wsdlCachePath)
    {
        $this->wsdlCachePath = $wsdlCachePath;
        $this->settings = null;

        return $this;
    }

    /**
     * @param boolean $isWsiMode
     *
     * @return MagentoTransport
     */
    public function setIsWsiMode($isWsiMode)
    {
        $this->isWsiMode = $isWsiMode;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsWsiMode()
    {
        return $this->isWsiMode;
    }
}
