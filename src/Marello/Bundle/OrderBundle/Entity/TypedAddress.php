<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_order_typed_address")
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="region",
 *          joinColumns={
 *              @ORM\JoinColumn(name="region_code", referencedColumnName="combined_code", nullable=true)
 *          }
 *      )
 * })
 */
class TypedAddress extends AbstractTypedAddress
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $phone;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

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
}
