<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\CustomerBundle\Entity\Customer;

class CustomerIdentityDataDTO
{
    /** @var string */
    protected $email;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(string $email, string $firstName, string $lastName)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param Customer $customer
     * @return static
     */
    public static function createFromMarelloCustomer(Customer $customer): self
    {
        return new self(
            $customer->getEmail(),
            $customer->getFirstName(),
            $customer->getLastName()
        );
    }
}
