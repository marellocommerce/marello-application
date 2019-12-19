<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "regular" = "Marello\Bundle\ServicePointBundle\Entity\TimePeriod",
 *     "override" = "Marello\Bundle\ServicePointBundle\Entity\TimePeriodOverride"
 * })
 * @ORM\Table(
 *     name="marello_sp_timeperiod",
 *     indexes={
 *         @ORM\Index(columns={"open_time", "close_time"}),
 *     }
 * )
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
abstract class AbstractTimePeriod implements DatesAwareInterface
{
    use DatesAwareTrait;

    const TYPE_REGULAR = 'regular';
    const TYPE_OVERRIDE = 'override';

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
     * @var string;
     */
    protected $timePeriodType = self::TYPE_REGULAR;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="open_time", type="time")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.timeperiod.open_time.label"
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
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
     *     },
     *     "dataaudit"={
     *         "auditable"=true,
     *     }
     * })
     */
    protected $closeTime;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTimePeriodType(): string
    {
        return $this->timePeriodType;
    }

    /**
     * @param string $timePeriodType
     * @return AbstractTimePeriod
     */
    public function setTimePeriodType(string $timePeriodType): AbstractTimePeriod
    {
        $this->timePeriodType = $timePeriodType;

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
     * @return AbstractTimePeriod
     */
    public function setOpenTime(\DateTime $openTime): AbstractTimePeriod
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
     * @return AbstractTimePeriod
     */
    public function setCloseTime(\DateTime $closeTime): AbstractTimePeriod
    {
        $this->closeTime = $closeTime;

        return $this;
    }
}
