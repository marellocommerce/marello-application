<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 1-3-18
 * Time: 12:02
 */

namespace Marello\Bundle\MageBridgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Class MagentoRestTransport
 * @package Marello\Bundle\MageBridgeBundle\Entity
 * @ORM\Entity
 * @Config()
 */
class MagentoRestTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="api_url", type="string", length=255, nullable=false)
     */
    protected $apiUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_url", type="string", length=255, nullable=false)
     */
    protected $adminUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=255, nullable=false)
     */
    protected $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=255, nullable=false)
     */
    protected $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="token_key", type="string", length=255, nullable=false)
     */
    protected $tokenKey;

    /**
     * @var string
     *
     * @ORM\Column(name="token_secret", type="string", length=255, nullable=false)
     */
    protected $tokenSecret;

    /**
     * @var array
     *
     * @ORM\Column(name="salesChannels", type="array")
     */
    protected $salesChannels = [];

    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @return array
     */
    public function getSalesChannels()
    {
        return $this->salesChannels;
    }

    /**
     * @param $salesChannels
     * @return $this
     */
    public function setSalesChannels($salesChannels)
    {
        $this->salesChannels = $salesChannels;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * @param string $adminUrl
     * @return $this
     */
    public function setAdminUrl($adminUrl)
    {
        $this->adminUrl = $adminUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenKey()
    {
        return $this->tokenKey;
    }

    /**
     * @param string $tokenKey
     * @return $this
     */
    public function setTokenKey($tokenKey)
    {
        $this->tokenKey = $tokenKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * @param $tokenSecret
     * @return $this
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->tokenSecret = $tokenSecret;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                array(
                    'apiUrl' => $this->getApiUrl(),
                    'adminUrl' => $this->getAdminUrl(),
                    'clientId' => $this->getClientId(),
                    'clientSecret' => $this->getClientSecret(),
                    'tokenKey' => $this->getTokenKey(),
                    'tokenSecret' => $this->getTokenSecret(),
                    'salesChannels' => $this->getSalesChannels(),
                )
            );
        }

        return $this->settings;
    }

}
