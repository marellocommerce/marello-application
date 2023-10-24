<?php

namespace Marello\Bundle\POSUserBundle\Api\Model;

class POSUserApi
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $roles;

    public function __construct(int $id, string $apiKey, array $roles)
    {
        $this->id = $id;
        $this->apiKey = $apiKey;
        $this->roles = $roles;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'apiKey' => $this->apiKey,
            'roles' => $this->roles
        ];
    }
}
