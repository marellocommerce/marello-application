<?php

namespace Marello\Bundle\TaxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * TaxRate
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\TaxBundle\Entity\Repository\TaxRateRepository")
 * @ORM\Table(name="marello_tax_tax_rate",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_tax_rate_codeidx",
 *              columns={"code"}
 *          )
 *      }
 * )
 * @Oro\Config(
 *      routeName="marello_tax_taxrate_index",
 *      routeView="marello_tax_taxrate_view",
 *      routeUpdate="marello_tax_taxrate_update",
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
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
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $code;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="percent", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $rate;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $data;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code;
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

    /**
     * @param array $data
     *
     * @return TaxRate
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
