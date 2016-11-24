<?php

namespace Marello\Bundle\OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_order_customer")
 * @Oro\Config(
 *      routeView="marello_order_customer_view",
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          }
 *      }
 * )
 */
class Customer implements FullNameInterface, EmailHolderInterface, EmailOwnerInterface
{
    use HasFullNameTrait, HasEmailAddressTrait;
    use EntityCreatedUpdatedAtTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @var MarelloAddress
     */
    protected $primaryAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $taxIdentificationNumber;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress",
     *     mappedBy="customer",
     *     cascade={"persist"}
     * )
     *
     * @var Collection|AbstractAddress[]
     */
    protected $addresses;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", nullable=false)
     *
     * @var Organization
     */
    protected $organization;

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
     * @param MarelloAddress $address
     *
     * @return Customer
     */
    public static function create($firstName, $lastName, $email, MarelloAddress $address)
    {
        $customer = new self();

        $customer
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPrimaryAddress($address)
        ;

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
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
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
        $this->primaryAddress = $primaryAddress;

        return $this;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
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
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }
}
