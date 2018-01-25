<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model;

use Oro\Bundle\UserBundle\Entity\UserApi;

class InstoreUserApi
{
    /** @var int $id */
    protected $id;

    /** @var string $apiKey */
    protected $apiKey;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }
}
