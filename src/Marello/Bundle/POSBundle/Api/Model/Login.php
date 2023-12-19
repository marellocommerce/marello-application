<?php

namespace Marello\Bundle\POSBundle\Api\Model;

/**
 * The model for frontend API resource to retrieve API access key by pos user email/username and password.
 */
class Login
{
    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $apiKey;

    /**
     * @var array
     */
    private $roles;

    /**
     * Gets the user.
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param string $user
     * @return $this
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gets the API access key that should be used for subsequent API requests.
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Sets the API access key belongs to the pos user with the given email and password.
     *
     * @param string|null $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey = null): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Set the pos user's roles.
     *
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles = []): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the pos user's roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'roles' => $this->roles
        ];
    }
}
