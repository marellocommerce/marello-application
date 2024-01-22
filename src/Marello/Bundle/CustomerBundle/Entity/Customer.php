<?php

namespace Marello\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marello_customer_customer",
 *       uniqueConstraints={
 *           @ORM\UniqueConstraint(
 *               name="marello_customer_emailorgidx",
 *               columns={"email","organization_id"}
 *           )
 *       }
 * )
 * @Oro\Config(
 *      routeView="marello_customer_view",
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          },
 *          "grid"={
 *              "default"="marello-customer-select-grid"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class Customer implements
    FullNameInterface,
    EmailHolderInterface,
    EmailOwnerInterface,
    OrganizationAwareInterface,
    ExtendEntityInterface
{
    use HasFullNameTrait, HasEmailAddressTrait;
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
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
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="primary_address_id", nullable=true)
     *
     * @var MarelloAddress
     *
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $primaryAddress;

    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="shipping_address_id", nullable=true)
     *
     * @var MarelloAddress
     *
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $shippingAddress;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress",
     *     mappedBy="customer",
     *     cascade={"persist"}
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Collection|AbstractAddress[]
     */
    protected $addresses;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\CustomerBundle\Entity\Company", inversedBy="customers")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
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
