<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendServicePointFacility;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_sp_servicepoint_fac")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *     routeName="marello_servicepoint_servicepoint_view",
 *     routeView="marello_servicepoint_servicepointfacility_view",
 *     routeCreate="marello_servicepoint_servicepointfacility_create",
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.servicepoint_facility.entity_label",
 *             "plural_label"="marello.servicepoint.servicepoint_facility.entity_plural_label"
 *         },
 *         "dataaudit"={
 *             "auditable"=true
 *         },
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         },
 *     }
 * )
 */
class ServicePointFacility extends ExtendServicePointFacility implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var ServicePoint
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePoint", inversedBy="servicePointFacilities")
     * @ORM\JoinColumn(name="service_point_id", nullable=false)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.service_point.label"
     *     }
     * })
     */
    protected $servicePoint;

    /**
     * @var Facility
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\Facility")
     * @ORM\JoinColumn(name="facility_id", nullable=false)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.facility.label"
     *     }
     * })
     */
    protected $facility;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.phone.label"
     *     }
     * })
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.email.label"
     *     }
     * })
     */
    protected $email;

    /**
     * @var Collection|TimePeriod[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\ServicePointBundle\Entity\TimePeriod", mappedBy="servicePointFacility", cascade={"ALL"})
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.servicepoint_facility.business_hours.label"
     *     }
     * })
     */
    protected $businessHours;

    public function __construct()
    {
        $this->businessHours = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ServicePoint
     */
    public function getServicePoint(): ?ServicePoint
    {
        return $this->servicePoint;
    }

    /**
     * @param ServicePoint $servicePoint
     * @return ServicePointFacility
     */
    public function setServicePoint(ServicePoint $servicePoint): ServicePointFacility
    {
        $this->servicePoint = $servicePoint;

        return $this;
    }

    /**
     * @return ?Facility
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility $facility
     * @return ServicePointFacility
     */
    public function setFacility(Facility $facility): ServicePointFacility
    {
        $this->facility = $facility;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return ServicePointFacility
     */
    public function setPhone(?string $phone): ServicePointFacility
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ServicePointFacility
     */
    public function setEmail(?string $email): ServicePointFacility
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|TimePeriod[]
     */
    public function getBusinessHours(): Collection
    {
        return $this->businessHours;
    }

    /**
     * @param Collection|TimePeriod[] $businessHours
     * @return ServicePointFacility
     */
    public function setBusinessHours(Collection $businessHours): ServicePointFacility
    {
        $this->businessHours = $businessHours;
        foreach ($this->businessHours as $businessHour) {
            $businessHour->setServicePointFacility($this);
        }

        return $this;
    }
}
