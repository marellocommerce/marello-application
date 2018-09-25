<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use MarelloEnterprise\Bundle\AddressBundle\Model\ExtendMarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Entity\HasGeocodeTrait;
use MarelloEnterprise\Bundle\GoogleApiBundle\Model\GeocodeAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity(
 *     repositoryClass="MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="marelloenterprise_address")
 * @Oro\Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class MarelloEnterpriseAddress extends ExtendMarelloEnterpriseAddress implements GeocodeAwareInterface
{
    use HasGeocodeTrait;
    use EntityCreatedUpdatedAtTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var MarelloAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\AddressBundle\Entity\MarelloAddress")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $address;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return MarelloAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param MarelloAddress $address
     * @return $this
     */
    public function setAddress(MarelloAddress $address)
    {
        $this->address = $address;
        
        return $this;
    }
}
