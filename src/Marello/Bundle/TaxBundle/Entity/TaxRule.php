<?php

namespace Marello\Bundle\TaxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * TaxRule
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\TaxBundle\Entity\Repository\TaxRuleRepository")
 * @ORM\Table(name="marello_tax_tax_rule")
 * @Oro\Config()
 * @ORM\HasLifecycleCallbacks()
 */
class TaxRule
{
    use EntityCreatedUpdatedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="includes_vat", type="boolean", nullable=false)
     */
    protected $includesVat;

    /**
     * @var TaxCode
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\TaxBundle\Entity\TaxCode")
     * @ORM\JoinColumn(name="tax_code_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $taxCode;

    /**
     * @var TaxRate
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\TaxBundle\Entity\TaxRate")
     * @ORM\JoinColumn(name="tax_rate_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $taxRate;

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
     * Set includesVat
     *
     * @param boolean $includesVat
     *
     * @return TaxRule
     */
    public function setIncludesVat($includesVat)
    {
        $this->includesVat = $includesVat;

        return $this;
    }

    /**
     * Get includesVat
     *
     * @return boolean
     */
    public function getIncludesVat()
    {
        return $this->includesVat;
    }

    /**
     * Set taxCode
     *
     * @param \Marello\Bundle\TaxBundle\Entity\TaxCode $taxCode
     *
     * @return TaxRule
     */
    public function setTaxCode(\Marello\Bundle\TaxBundle\Entity\TaxCode $taxCode)
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    /**
     * Get taxCode
     *
     * @return \Marello\Bundle\TaxBundle\Entity\TaxCode
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * Set taxRate
     *
     * @param \Marello\Bundle\TaxBundle\Entity\TaxRate $taxRate
     *
     * @return TaxRule
     */
    public function setTaxRate(\Marello\Bundle\TaxBundle\Entity\TaxRate $taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get taxRate
     *
     * @return \Marello\Bundle\TaxBundle\Entity\TaxRate
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }
}
