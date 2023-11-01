<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

use Marello\Bundle\ShippingBundle\Model\ExtendTrackingInfo;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_tracking_info",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="marello_tracking_info_shipmentidx",
 *             columns={"shipment_id"}
 *         )
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *  defaultValues={
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
class TrackingInfo extends ExtendTrackingInfo
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
     * @ORM\Column(name="tracking_url", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $trackingUrl;

    /**
     * @ORM\Column(name="track_trace_url", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $trackTraceUrl;

    /**
     * @ORM\Column(name="tracking_code", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $trackingCode;

    /**
     * @ORM\Column(name="provider", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $provider;

    /**
     * @ORM\Column(name="provider_name", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var string
     */
    protected $providerName;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Marello\Bundle\ShippingBundle\Entity\Shipment",
     *     inversedBy="trackingInfo",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     * @var Shipment
     */
    protected $shipment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    /**
     * @param string $trackingUrl
     * @return $this
     */
    public function setTrackingUrl(string $trackingUrl): self
    {
        $this->trackingUrl = $trackingUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackTraceUrl(): ?string
    {
        return $this->trackTraceUrl;
    }

    /**
     * @param string $trackTraceUrl
     * @return $this
     */
    public function setTrackTraceUrl(string $trackTraceUrl): self
    {
        $this->trackTraceUrl = $trackTraceUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackingCode(): ?string
    {
        return $this->trackingCode;
    }

    /**
     * @param string $trackingCode
     * @return $this
     */
    public function setTrackingCode(string $trackingCode): self
    {
        $this->trackingCode = $trackingCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     * @return $this
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     * @return $this
     */
    public function setProviderName(string $providerName): self
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * @return Shipment|null
     */
    public function getShipment(): ?Shipment
    {
        return $this->shipment;
    }

    /**
     * @param Shipment $shipment
     * @return $this
     */
    public function setShipment(Shipment $shipment): self
    {
        $this->shipment = $shipment;
        $this->shipment->setTrackingInfo($this);

        return $this;
    }
}
