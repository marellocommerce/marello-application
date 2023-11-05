<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

trait PriceDatesTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $endDate;

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isDateAvailable(\DateTime $dateTime)
    {
        $available = true;
        if ($this->startDate && $dateTime < $this->startDate) {
            $available = false;
        }
        if ($this->endDate && $dateTime > $this->endDate) {
            $available = false;
        }

        return $available;
    }
}
