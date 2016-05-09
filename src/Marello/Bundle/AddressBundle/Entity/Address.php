<?php

namespace Marello\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_address")
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="region",
 *          joinColumns={
 *              @ORM\JoinColumn(name="region_code", referencedColumnName="combined_code", nullable=true)
 *          }
 *      )
 * })
 */
class Address extends AbstractAddress
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $phone;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Customer", inversedBy="addresses")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     *
     * @return $this
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }
}
