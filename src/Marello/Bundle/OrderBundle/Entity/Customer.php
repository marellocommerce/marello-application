<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\HasEmailAddressTrait;
use Marello\Bundle\CustomerBundle\Entity\HasFullNameTrait;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;

/**
 * @deprecated, use "Marello\Bundle\CustomerBundle\Entity\Customer" instead
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_order_customer")
 */
class Customer implements
    FullNameInterface,
    EmailHolderInterface,
    EmailOwnerInterface,
    ExtendEntityInterface
{
    use HasFullNameTrait, HasEmailAddressTrait;
    use EntityCreatedUpdatedAtTrait;
    use ExtendEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     */
    protected $primaryAddress;

    /**
     */
    protected $shippingAddress;

    /**
      *
     * @var string
     */
    protected $taxIdentificationNumber;

    /**
     * @var Collection|AbstractAddress[]
     */
    protected $addresses;

    /**
     */
    protected $company;

    /**
     * Customer constructor.
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    /**
     * @param string  $firstName
     * @param string  $lastName
     * @param string  $email
     * @param MarelloAddress $primaryAddress
     * @param MarelloAddress $shippingAddress
     *
     * @return Customer
     */
    public static function create(
        $firstName,
        $lastName,
        $email,
        MarelloAddress $primaryAddress,
        MarelloAddress $shippingAddress = null
    ) {
        $customer = new self();

        $customer
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPrimaryAddress($primaryAddress)
        ;

        if ($shippingAddress) {
            $customer->setShippingAddress($shippingAddress);
        }

        return $customer;
    }

    /**
     * Get entity unique id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|\Oro\Bundle\AddressBundle\Entity\AbstractAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return $this
     */
    public function addAddress(MarelloAddress $address)
    {
        $this->addresses->add($address->setCustomer($this));

        return $this;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return $this
     */
    public function removeAddress(MarelloAddress $address)
    {
        $this->addresses->removeElement($address->setCustomer(null));

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getPrimaryAddress()
    {
        return $this->primaryAddress;
    }

    /**
     * @param MarelloAddress $primaryAddress
     *
     * @return $this
     */
    public function setPrimaryAddress(MarelloAddress $primaryAddress)
    {
        $primaryAddress->setCustomer($this);
        $this->primaryAddress = $primaryAddress;

        return $this;
    }

    /**
     * @param MarelloAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(MarelloAddress $shippingAddress)
    {
        $shippingAddress->setCustomer($this);
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return string
     */
    public function getTaxIdentificationNumber()
    {
        return $this->taxIdentificationNumber;
    }

    /**
     * @param string $taxIdentificationNumber
     *
     * @return $this
     */
    public function setTaxIdentificationNumber($taxIdentificationNumber)
    {
        $this->taxIdentificationNumber = $taxIdentificationNumber;

        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     * @return $this
     */
    public function setCompany(Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }
}
