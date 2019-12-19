<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendTimePeriodOverride;
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
class TimePeriodOverride extends ExtendTimePeriodOverride
{
    /**
     * @var ?BusinessHoursOverride
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\BusinessHoursOverride", inversedBy="timePeriods")
     * @ORM\JoinColumn(name="business_hours_override_id", nullable=true, onDelete="CASCADE")
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

        $this->timePeriodType = self::TYPE_OVERRIDE;
    }

    /**
     * @return ?BusinessHoursOverride
     */
    public function getBusinessHours(): ?BusinessHoursOverride
    {
        return $this->businessHours;
    }

    /**
     * @param BusinessHoursOverride $businessHours
     * @return TimePeriodOverride
     */
    public function setBusinessHours(BusinessHoursOverride $businessHours): TimePeriodOverride
    {
        $this->businessHours = $businessHours;

        return $this;
    }
}
