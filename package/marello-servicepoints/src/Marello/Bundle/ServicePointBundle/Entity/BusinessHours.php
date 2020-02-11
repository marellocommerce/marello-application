<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendBusinessHours;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_sp_businesshours",
 *     indexes={
 *         @ORM\Index(columns={"day_of_week"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *              name="uniq_marello_spf_day_of_week",
 *              columns={"servicepoint_facility_id", "day_of_week"})
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
class BusinessHours extends ExtendBusinessHours implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var ?int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.id.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=false,
     *     }
     * })
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="day_of_week", type="integer")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.businesshours.day_of_week.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $dayOfWeek = 0;

    /**
     * @var Collection|TimePeriod[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ServicePointBundle\Entity\TimePeriod",
     *     mappedBy="businessHours",
     *     cascade={"ALL"}
     * )
     * @ORM\OrderBy({"closeTime": "ASC"})
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
     * @ORM\ManyToOne(
     *     targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility",
     *     inversedBy="businessHours"
     * )
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
     * @return int
     */
    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    /**
     * @param int $dayOfWeek
     * @return BusinessHours
     */
    public function setDayOfWeek(int $dayOfWeek): BusinessHours
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * @return Collection|TimePeriod[]
     */
    public function getTimePeriods(): Collection
    {
        return $this->timePeriods;
    }

    /**
     * @param TimePeriod $timePeriod
     * @return BusinessHours
     */
    public function addTimePeriod(TimePeriod $timePeriod): BusinessHours
    {
        if (!$this->timePeriods->contains($timePeriod)) {
            $this->timePeriods[] = $timePeriod;
        }

        return $this;
    }

    /**
     * @param TimePeriod $timePeriod
     * @return BusinessHours
     */
    public function removeTimePeriod(TimePeriod $timePeriod): BusinessHours
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
     * @return BusinessHours
     */
    public function setServicePointFacility(ServicePointFacility $servicePointFacility): BusinessHours
    {
        $this->servicePointFacility = $servicePointFacility;

        return $this;
    }
}
