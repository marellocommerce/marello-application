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
     * @ORM\Column(name="marello_magento_url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                array(
//                    'email'            => $this->getEmail(),
                    'url'              => $this->getUrl(),
//                    'token'            => $this->getToken(),
//                    'zendeskUserEmail' => $this->getZendeskUserEmail()
                )
            );
        }

        return $this->settings;
    }
}
