<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\ShippingBundle\Model\ExtendShipment;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_shipment")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="organization",
 *          "owner_column_name"="organization_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Shipment extends ExtendShipment implements OrganizationAwareInterface
{
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
     * @ORM\Column(name="shipping_service", type="string", length=255)
     *
     * @var string
     */
    protected $shippingService;

    /**
     * @ORM\Column(name="ups_shipment_digest", type="text", nullable=true)
     *
     * @var string
     */
    protected $upsShipmentDigest;

    /**
     * @ORM\Column(name="identification_number", type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $identificationNumber;

    /**
     * @ORM\Column(name="ups_package_tracking_number", type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $upsPackageTrackingNumber;

    /**
     * @ORM\Column(name="base64_encoded_label", type="text", nullable=true)
     *
     * @var string
     */
    protected $base64EncodedLabel;
    
    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * @param string $shippingService
     *
     * @return $this
     */
    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpsShipmentDigest()
    {
        return $this->upsShipmentDigest;
    }

    /**
     * @param string $upsShipmentDigest
     *
     * @return $this
     */
    public function setUpsShipmentDigest($upsShipmentDigest)
    {
        $this->upsShipmentDigest = $upsShipmentDigest;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificationNumber()
    {
        return $this->identificationNumber;
    }

    /**
     * @param string $identificationNumber
     *
     * @return $this
     */
    public function setIdentificationNumber($identificationNumber)
    {
        $this->identificationNumber = $identificationNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpsPackageTrackingNumber()
    {
        return $this->upsPackageTrackingNumber;
    }

    /**
     * @param string $upsPackageTrackingNumber
     *
     * @return $this
     */
    public function setUpsPackageTrackingNumber($upsPackageTrackingNumber)
    {
        $this->upsPackageTrackingNumber = $upsPackageTrackingNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getBase64EncodedLabel()
    {
        return $this->base64EncodedLabel;
    }

    /**
     * @param string $base64EncodedLabel
     *
     * @return $this
     */
    public function setBase64EncodedLabel($base64EncodedLabel)
    {
        $this->base64EncodedLabel = $base64EncodedLabel;

        return $this;
    }

    /**
     * @return OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param OrganizationInterface $organization
     * @return $this
     */
    public function setOrganization(OrganizationInterface $organization)
    {
        $this->organization = $organization;

        return $this;
    }
}
