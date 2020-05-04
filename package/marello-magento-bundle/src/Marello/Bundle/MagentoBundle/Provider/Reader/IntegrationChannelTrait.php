<?php
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
