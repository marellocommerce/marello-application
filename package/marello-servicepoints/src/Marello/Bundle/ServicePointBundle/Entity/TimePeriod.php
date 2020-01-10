<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendTimePeriod;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
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
 *         }
 *     }
 * )
 */
class TimePeriod extends ExtendTimePeriod
{
    /**
     * @var ?BusinessHours
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\BusinessHours", inversedBy="timePeriods")
     * @ORM\JoinColumn(name="business_hours_id", nullable=true, onDelete="CASCADE")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.business_hours.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $businessHours;

    public function __construct()
    {
        parent::__construct();

        $this->timePeriodType = self::TYPE_REGULAR;
    }

    /**
     * @return ?BusinessHours
     */
    public function getBusinessHours(): ?BusinessHours
    {
        return $this->businessHours;
    }

    /**
     * @param mixed $businessHours
     * @return TimePeriod
     */
    public function setBusinessHours(BusinessHours $businessHours): TimePeriod
    {
        $this->businessHours = $businessHours;

        return $this;
    }
}
