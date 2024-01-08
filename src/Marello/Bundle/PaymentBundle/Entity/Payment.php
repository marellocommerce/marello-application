<?php

namespace Marello\Bundle\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity()
 * @Oro\Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 * @ORM\Table(
 *      name="marello_payment_payment"
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Payment implements
    OrganizationAwareInterface,
    ExtendEntityInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;
    
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var AbstractInvoice
     */
    protected $paymentSource;

    /**
     * @var int
     *
     * @ORM\Column(name="total_paid", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $totalPaid = 0;

    /**
     * @var string
     * @ORM\Column(name="payment_method", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentMethod;

    /**
     * @var array $paymentMethodOptions
     *
     * @ORM\Column(name="payment_method_options", type="json_array", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentMethodOptions;

    /**
     * @var string
     * @ORM\Column(name="payment_reference", type="string", length=255, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentReference;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_details", type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentDetails;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $paymentDate;

    /**
     * @var string
     * @ORM\Column(name="currency", type="string", length=10, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * @var \Extend\Entity\EV_Marello_Payment_Status
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $status;

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AbstractInvoice|null
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param AbstractInvoice|null $paymentSource
     * @return $this
     */
    public function setPaymentSource(AbstractInvoice $paymentSource = null)
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPaid()
    {
        return $this->totalPaid;
    }

    /**
     * @param int $totalPaid
     *
     * @return $this
     */
    public function setTotalPaid($totalPaid)
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodOptions()
    {
        return $this->paymentMethodOptions;
    }

    /**
     * @param array $paymentMethodOptions
     * @return $this
     */
    public function setPaymentMethodOptions(array $paymentMethodOptions = [])
    {
        $this->paymentMethodOptions = $paymentMethodOptions;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    /**
     * @param string $paymentDetails
     *
     * @return $this
     */
    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * @param string|null $paymentReference
     *
     * @return $this
     */
    public function setPaymentReference(string $paymentReference = null)
    {
        $this->paymentReference = $paymentReference;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate ? : $this->createdAt;
    }

    /**
     * @param \DateTime|null $paymentDate
     * @return $this
     */
    public function setPaymentDate(\DateTime $paymentDate = null)
    {
        $this->paymentDate = $paymentDate;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return \Extend\Entity\EV_Marello_Payment_Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
