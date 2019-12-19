<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendBusinessHoursOverride;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_sp_bhoverride",
 *     indexes={
 *         @ORM\Index(columns={"date"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"servicepoint_facility_id", "date"})
 *     }
 * )
 * @Config(
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.businesshours.entity_label",
 *             "plural_label"="marello.servicepoint.businesshours.entity_plural_label"
 *         },
 *         "dataaudit"={
 *             "auditable"=true
 *         },
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         }
 *     }
 * )
 */
class BusinessHoursOverride extends ExtendBusinessHoursOverride implements DatesAwareInterface
{
    use DatesAwareTrait;

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    /**
     * @var ?int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.date.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(name="open_status", type="string", length=6)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.open_status.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $openStatus = self::STATUS_OPEN;

    /**
     * @var ArrayCollection|TimePeriodOverride[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\ServicePointBundle\Entity\TimePeriodOverride", mappedBy="businessHours", cascade={"ALL"})
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.time_periods.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $timePeriods;

    /**
     * @var ServicePointFacility
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility", inversedBy="businessHoursOverrides")
     * @ORM\JoinColumn(name="servicepoint_facility_id", nullable=false, onDelete="CASCADE")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.servicepoint_facility.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $servicePointFacility;

    public function __construct()
    {
        $this->timePeriods = new ArrayCollection();
        $this->date = new \DateTime();

        parent::__construct();
    }

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return BusinessHoursOverride
     */
    public function setDate(\DateTime $date): BusinessHoursOverride
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getOpenStatus(): string
    {
        return $this->openStatus;
    }

    /**
     * @param string $openStatus
     * @return BusinessHoursOverride
     */
    public function setOpenStatus(string $openStatus): BusinessHoursOverride
    {
        $this->openStatus = $openStatus;

        return $this;
    }

    /**
     * @return ArrayCollection|TimePeriodOverride[]
     */
    public function getTimePeriods()
    {
        return $this->timePeriods;
    }

    /**
     * @param TimePeriodOverride $timePeriod
     * @return BusinessHoursOverride
     */
    public function addTimePeriod(TimePeriodOverride $timePeriod): BusinessHoursOverride
    {
        if (!$this->timePeriods->contains($timePeriod)) {
            $this->timePeriods[] = $timePeriod;
        }

        return $this;
    }

    /**
     * @param TimePeriodOverride $timePeriod
     * @return BusinessHoursOverride
     */
    public function removeTimePeriod(TimePeriodOverride $timePeriod): BusinessHoursOverride
    {
        $this->timePeriods->removeElement($timePeriod);

        return $this;
    }

    /**
     * @return ServicePointFacility
     */
    public function getServicePointFacility(): ServicePointFacility
    {
        return $this->servicePointFacility;
    }

    /**
     * @param ServicePointFacility $servicePointFacility
     * @return BusinessHoursOverride
     */
    public function setServicePointFacility(ServicePointFacility $servicePointFacility): BusinessHoursOverride
    {
        $this->servicePointFacility = $servicePointFacility;

        return $this;
    }
}
