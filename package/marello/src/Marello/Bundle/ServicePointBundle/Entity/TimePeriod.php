<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendTimePeriod;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_sp_timeperiod",
 *     indexes={
 *         @ORM\Index(columns={"day_of_week"}),
 *         @ORM\Index(columns={"open_time", "close_time"}),
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.timeperiod.entity_label",
 *             "plural_label"="marello.servicepoint.timeperiod.entity_plural_label"
 *         },
 *         "dataaudit"={
 *             "auditable"=true
 *         },
 *         "security"={
 *             "type"="ACL",
 *             "group_name"=""
 *         },
 *         "form"={
 *             "form_type"="Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermSelectType",
 *             "grid_name"="marello-payment-terms-select-grid",
 *         }
 *     }
 * )
 */
class TimePeriod extends ExtendTimePeriod implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var integer;
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="day_of_week", type="integer")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.day_of_week.label"
     *     }
     * })
     */
    protected $dayOfWeek;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="open_time", type="time")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.open_time.label"
     *     }
     * })
     */
    protected $openTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="close_time", type="time")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.close_time.label"
     *     }
     * })
     */
    protected $closeTime;

    /**
     * @var ServicePointFacility
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility", inversedBy="businessHours")
     * @ORM\JoinColumn(name="servicepoint_facility_id", nullable=false, onDelete="CASCADE")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.service_point_facility.label"
     *     }
     * })
     */
    protected $servicePointFacility;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    /**
     * @param int $dayOfWeek
     * @return TimePeriod
     */
    public function setDayOfWeek(int $dayOfWeek): TimePeriod
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOpenTime(): ?\DateTime
    {
        return $this->openTime;
    }

    /**
     * @param \DateTime $openTime
     * @return TimePeriod
     */
    public function setOpenTime(\DateTime $openTime): TimePeriod
    {
        $this->openTime = $openTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCloseTime(): ?\DateTime
    {
        return $this->closeTime;
    }

    /**
     * @param \DateTime $closeTime
     * @return TimePeriod
     */
    public function setCloseTime(\DateTime $closeTime): TimePeriod
    {
        $this->closeTime = $closeTime;

        return $this;
    }

    /**
     * @return ?ServicePointFacility
     */
    public function getServicePointFacility(): ?ServicePointFacility
    {
        return $this->servicePointFacility;
    }

    /**
     * @param ServicePointFacility $servicePointFacility
     * @return TimePeriod
     */
    public function setServicePointFacility(ServicePointFacility $servicePointFacility): TimePeriod
    {
        $this->servicePointFacility = $servicePointFacility;

        return $this;
    }
}
