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
     * @ORM\Column(name="marello_magento_client_id", type="string", length=255, nullable=false)
     */
    protected $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="marello_magento_client_secret", type="string", length=255, nullable=false)
     */
    protected $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="marello_magento_token", type="string", length=255, nullable=false)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="marello_magento_token_secret", type="string", length=255, nullable=false)
     */
    protected $tokenSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="marello_magento_infos_url", type="string", length=255, nullable=false)
     */
    protected $infosUrl;

    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * @param mixed $tokenSecret
     * @return $this
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->tokenSecret = $tokenSecret;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfosUrl()
    {
        return $this->infosUrl;
    }

    /**
     * @param mixed $infosUrl
     * @return $this
     */
    public function setInfosUrl($infosUrl)
    {
        $this->infosUrl = $infosUrl;

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
                    'infosUrl' => $this->getInfosUrl(),
                    'clientId' => $this->getClientId(),
                    'clientSecret' => $this->getClientSecret(),
                    'token' => $this->getToken(),
                    'tokenSecret' => $this->getTokenSecret()
                )
            );
        }

        return $this->settings;
    }
}
