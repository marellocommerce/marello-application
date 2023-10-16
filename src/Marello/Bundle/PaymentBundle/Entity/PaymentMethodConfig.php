<?php

namespace Marello\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodConfigRepository")
 * @ORM\Table(name="marello_payment_method_config")
 * @Config()
 */
class PaymentMethodConfig implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var PaymentMethodsConfigsRule
     *
     * @ORM\ManyToOne(
     *     targetEntity="Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule",
     *     inversedBy="methodConfigs"
     * )
     * @ORM\JoinColumn(name="configs_rule_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $methodsConfigsRule;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=255, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10
     *          }
     *      }
     * )
     */
    protected $method;

    /**
     * @var array
     *
     * @ORM\Column(name="options", type="array", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $options = [];

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PaymentMethodsConfigsRule
     */
    public function getMethodsConfigsRule()
    {
        return $this->methodsConfigsRule;
    }

    /**
     * @param PaymentMethodsConfigsRule $methodsConfigsRule
     * @return $this
     */
    public function setMethodsConfigsRule($methodsConfigsRule)
    {
        $this->methodsConfigsRule = $methodsConfigsRule;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}
