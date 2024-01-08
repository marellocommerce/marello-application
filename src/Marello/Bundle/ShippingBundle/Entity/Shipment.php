<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_shipment",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_shipment_trackinginfoidx",
 *              columns={"tracking_info_id"}
 *          )
 *      }
 * )
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
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class Shipment implements OrganizationAwareInterface, ExtendEntityInterface
{
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
     * @ORM\Column(name="shipping_service", type="string", length=255)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $shippingService;

    /**
     * @ORM\Column(name="ups_shipment_digest", type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $upsShipmentDigest;

    /**
     * @ORM\Column(name="identification_number", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $identificationNumber;

    /**
     * @ORM\Column(name="ups_package_tracking_number", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
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
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ShippingBundle\Entity\TrackingInfo", cascade={"persist"}, mappedBy="shipment")
     * @ORM\JoinColumn(name="tracking_info_id", nullable=true)
     *
     * @var TrackingInfo
     */
    protected $trackingInfo;

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
     * @return TrackingInfo|null
     */
    public function getTrackingInfo(): ?TrackingInfo
    {
        return $this->trackingInfo;
    }

    /**
     * @param TrackingInfo|null $trackingInfo
     *
     * @return $this
     */
    public function setTrackingInfo(TrackingInfo $trackingInfo = null): self
    {
        $this->trackingInfo = $trackingInfo;

        return $this;
    }
}
