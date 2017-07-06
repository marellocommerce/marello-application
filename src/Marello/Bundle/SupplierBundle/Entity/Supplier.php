<?php

namespace Marello\Bundle\SupplierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * Supplier
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\SupplierBundle\Entity\Repository\SupplierRepository")
 * @ORM\Table(name="marello_supplier_supplier")
 * @Oro\Config()
 * @ORM\HasLifecycleCallbacks()
 */
class Supplier
{
    use EntityCreatedUpdatedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     */
    protected $name;
    
    /**
     * @var MarelloAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $address = null;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true, nullable=true)
     */
    protected $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    protected $priority;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="can_dropship", type="boolean", nullable=false)
     */
    protected $canDropship = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    protected $isActive = true;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Supplier
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param MarelloAddress $address
     *
     * @return Supplier
     */
    public function setAddress(MarelloAddress $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return MarelloAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Supplier
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Supplier
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set canDropship
     *
     * @param boolean $canDropship
     *
     * @return Supplier
     */
    public function setCanDropship($canDropship)
    {
        $this->canDropship = $canDropship;

        return $this;
    }

    /**
     * Get canDropship
     *
     * @return boolean
     */
    public function getCanDropship()
    {
        return $this->canDropship;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Supplier
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
