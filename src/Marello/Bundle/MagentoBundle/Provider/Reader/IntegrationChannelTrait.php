<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 07/06/2018
 * Time: 14:21
 */

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

trait IntegrationChannelTrait
{
    /**
     * @var integrationChannelId
     */
    protected $integrationChannelId;

    /**
     * @return integrationChannelId
     */
    public function getIntegrationChannelId()
    {
        return $this->integrationChannelId;
    }

    /**
     * @param $integrationChannelId
     * @return $this
     */
    public function setIntegrationChannelId($integrationChannelId)
    {
        $this->integrationChannelId = $integrationChannelId;

        return $this;
    }
}
