<?php

namespace Marello\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * @ORM\Entity
 */
class Invoice extends AbstractInvoice implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    const INVOICE_TYPE = 'invoice';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     */
    protected $invoiceType = self::INVOICE_TYPE;

    /**
     * @var Collection|InvoiceItem[]
     *
     * @ORM\OneToMany(targetEntity="InvoiceItem", mappedBy="invoice", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $items;

    /**
     * @var PaymentTerm
     *
     * @ORM\ManyToONe(targetEntity="Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm")
     * @ORM\JoinColumn(name="payment_term_id", nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *     defaultValues={
     *         "email"={
     *             "available_in_template"=true
     *         },
     *         "dataaudit"={
     *             "auditable"=true
     *         }
     *     }
     * )
     */
    protected $paymentTerm;

    /**
     * @return PaymentTerm|null
     */
    public function getPaymentTerm()
    {
        return $this->paymentTerm;
    }

    /**
     * @param PaymentTerm|null $paymentTerm
     * @return Invoice
     */
    public function setPaymentTerm(PaymentTerm $paymentTerm = null)
    {
        $this->paymentTerm = $paymentTerm;

        return $this;
    }
}
