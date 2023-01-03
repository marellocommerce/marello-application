<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderToInvoiceMapper extends AbstractInvoiceMapper
{
    protected $paymentTermProvider;

    /**
     * OrderToInvoiceMapper constructor.
     * @param EntityFieldProvider $entityFieldProvider
     * @param PropertyAccessorInterface $propertyAccessor
     * @param DoctrineHelper $doctrineHelper
     * @param PaymentTermProvider $paymentTermProvider
     */
    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        DoctrineHelper $doctrineHelper,
        PaymentTermProvider $paymentTermProvider
    ) {
        parent::__construct($entityFieldProvider, $propertyAccessor, $doctrineHelper);

        $this->paymentTermProvider = $paymentTermProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Order)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to OrderToInvoiceMapper', get_class($sourceEntity))
            );
        }
        $paymentTerm = $this->getPaymentTerm($sourceEntity);

        /** @var Order $sourceEntity */
        $invoice = new Invoice();
        $data = $this->getData($sourceEntity, Invoice::class);
        $data['order'] = $sourceEntity;
        $data['items'] = $this->getItems($sourceEntity->getItems());
        $data['payment_term'] = $paymentTerm;
        $data['invoice_due_date'] = $this->getInvoiceDueDate($sourceEntity, $paymentTerm);
        $data['total_due'] = $sourceEntity->getGrandTotal();
        if ($data['invoicedAt'] === null) {
            $data['invoicedAt'] = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        $this->assignData($invoice, $data);

        return $invoice;
    }

    /**
     * @param Collection $items
     * @return ArrayCollection
     */
    protected function getItems(Collection $items)
    {
        $orderItems = $items->toArray();
        $invoiceItems = [];
        /** @var OrderItem $item */
        foreach ($orderItems as $item) {
            $invoiceItems[] = $this->mapItem($item);
        }

        return new ArrayCollection($invoiceItems);
    }

    /**
     * @param OrderItem $orderItem
     * @return InvoiceItem
     */
    protected function mapItem(OrderItem $orderItem)
    {
        $invoiceItem = new InvoiceItem();
        $invoiceItemData = $this->getData($orderItem, InvoiceItem::class);
        $invoiceItemData['productUnit'] = $orderItem->getProductUnit() ? $orderItem->getProductUnit()->getId() : null;
        $this->assignData($invoiceItem, $invoiceItemData);

        return $invoiceItem;
    }

    /**
     * @param Order $sourceEntity
     * @return PaymentTerm|null
     */
    protected function getPaymentTerm(Order $sourceEntity)
    {
        return $this->paymentTermProvider->getCustomerPaymentTerm($sourceEntity->getCustomer());
    }

    /**
     * @param Order $sourceEntity
     * @param PaymentTerm|null $paymentTerm
     * @return \DateTime|null
     */
    protected function getInvoiceDueDate(Order $sourceEntity, PaymentTerm $paymentTerm = null)
    {
        if ($paymentTerm === null) {
            return null;
        }

        if ($sourceEntity->getInvoicedAt() !== null) {
            $dueDate = clone $sourceEntity->getInvoicedAt();
        } else {
            $dueDate = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        $dueDate->modify(sprintf('+%d days', $paymentTerm->getTerm()));

        return $dueDate;
    }
}
