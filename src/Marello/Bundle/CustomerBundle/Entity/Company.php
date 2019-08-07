<?php

namespace Marello\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\CustomerBundle\Model\ExtendCompany;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository")
 * @ORM\Table(name="marello_customer_company" )
 *
 * @Config(
 *      routeName="marello_customer_company_index",
 *      routeView="marello_customer_company_view",
 *      routeCreate="marello_customer_company_create",
 *      routeUpdate="marello_customer_company_update",
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
 *              "default"="marello-companies-grid"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Company extends ExtendCompany implements OrganizationAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *     defaultValues={
     *         "importexport"={
     *             "order"=10,
     *             "identity"=true
     *         }
     *     }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\CustomerBundle\Entity\Company", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "header"="Parent",
     *              "order"=30
     *          }
     *      }
     * )
     */
    protected $parent;

    /**
     * @var Collection|Company[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\CustomerBundle\Entity\Company", mappedBy="parent")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $children;

    /**
     * @var MarelloAddress[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", fetch="EAGER", cascade={"persist"}
     *     )
     * @ORM\JoinTable(name="marello_company_join_address",
     *      joinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id", unique=true)}
     *      )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $addresses;

    /**
     * @var Collection|Customer[]
     *
     * @ORM\OneToMany(
     *      targetEntity="Marello\Bundle\OrderBundle\Entity\Customer",
     *      mappedBy="company",
     *      cascade={"persist"}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     **/
    protected $customers;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Company $parent
     *
     * @return $this
     */
    public function setParent(Company $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Company
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return $this
     */
    public function addAddress(MarelloAddress $address)
    {
        if (!$this->getAddresses()->contains($address)) {
            $this->getAddresses()->add($address);
        }

        return $this;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return $this
     */
    public function removeAddress(MarelloAddress $address)
    {
        if ($this->hasAddress($address)) {
            $this->getAddresses()->removeElement($address);
        }

        return $this;
    }

    /**
     * @return Collection|MarelloAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param MarelloAddress $address
     *
     * @return bool
     */
    protected function hasAddress(MarelloAddress $address)
    {
        return $this->getAddresses()->contains($address);
    }

    /**
     * @param Company $child
     *
     * @return $this
     */
    public function addChild(Company $child)
    {
        if (!$this->hasChild($child)) {
            $child->setParent($this);
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @param Company $child
     *
     * @return $this
     */
    public function removeChild(Company $child)
    {
        if ($this->hasChild($child)) {
            $child->setParent(null);
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Company $child
     *
     * @return bool
     */
    protected function hasChild(Company $child)
    {
        return $this->children->contains($child);
    }

    /**
     * @param Customer $customer
     *
     * @return $this
     */
    public function addCustomer(Customer $customer)
    {
        if (!$this->hasCustomer($customer)) {
            $customer->setCompany($this);
            $this->customers->add($customer);
        }

        return $this;
    }

    /**
     * @param Customer $customer
     *
     * @return $this
     */
    public function removeCustomer(Customer $customer)
    {
        if ($this->hasCustomer($customer)) {
            $customer->setCompany(null);
            $this->customers->removeElement($customer);
        }

        return $this;
    }

    /**
     * @return Collection|Customer[]
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    protected function hasCustomer(Customer $customer)
    {
        return $this->customers->contains($customer);
    }
}
