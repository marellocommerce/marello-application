<?php

namespace Marello\Bundle\POSUserBundle\Api\Model;

class POSUserApi
{
    /** @var int $id */
    protected $id;

    /** @var string $apiKey */
    protected $apiKey;

    /**
     * @param int $id
     * @param string $apiKey
     */
    public function __construct($id, $apiKey)
    {
        $this->id = $id;
        $this->apiKey = $apiKey;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Implement to Array
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'apiKey' => $this->apiKey
        ];
    }
}