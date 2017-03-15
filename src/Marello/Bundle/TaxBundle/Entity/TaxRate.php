<?php

namespace Marello\Bundle\TaxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * TaxRate
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\TaxBundle\Entity\Repository\TaxRateRepository")
 * @ORM\Table(name="marello_tax_tax_rate")
 * @Oro\Config()
 * @ORM\HasLifecycleCallbacks()
 */
class TaxRate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, unique=true, nullable=false)
     */
    protected $code;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="float", nullable=false)
     */
    protected $rate;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code. ' '. $this->rate. '%';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return TaxRate
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set rate
     *
     * @param float $rate
     *
     * @return TaxRate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }
}
