<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Entity\CreditmemoItem;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceType;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class RefundToCreditmemoMapper extends AbstractInvoiceMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Refund)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to RefundToCreditmemoMapper', get_class($sourceEntity))
            );
        }
        /** @var Refund $sourceEntity */
        $invoice = new Creditmemo();
        $data = $this->getData($sourceEntity->getOrder(), Creditmemo::class);
        $data['order'] = $sourceEntity->getOrder();
        $data['items'] = $this->getItems($sourceEntity->getItems());
        $subtotal = 0.00;
        $totalTax = 0.00;
        /** @var InvoiceItem $item */
        foreach ($data['items'] as $item) {
            $subtotal += $item->getRowTotalExclTax();
            $totalTax += $item->getTax();
        }
        $data['subtotal'] = $subtotal;
        $data['totalTax'] = $totalTax;
        $data['grandTotal'] = $subtotal + $totalTax + $data['shippingAmountInclTax'];
        if ($data['invoicedAt'] === null) {
            $data['invoicedAt'] = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $data['type'] = $this->getInvoiceType(InvoiceType::CREDITMEMO_TYPE);
        $this->assignData($invoice, $data);

        return $invoice;
    }

    /**
     * @param Collection $items
     * @return ArrayCollection
     */
    protected function getItems(Collection $items)
    {
        $refundItems = $items->toArray();
        $invoiceItems = [];
        /** @var RefundItem $item */
        foreach ($refundItems as $item) {
            $invoiceItems[] = $this->mapItem($item);
        }

        return new ArrayCollection($invoiceItems);
    }

    /**
     * @param RefundItem $refundItem
     * @return CreditmemoItem
     */
    protected function mapItem(RefundItem $refundItem)
    {
        $invoiceItem = new CreditmemoItem();
        $invoiceItemData = $this->getData($refundItem->getOrderItem(), InvoiceItem::class);
        $quantity = $refundItem->getQuantity();
        $tax = $invoiceItemData['tax']/$invoiceItemData['quantity']*$quantity;

        $rowTotalExclTax =  $invoiceItemData['rowTotalExclTax']/$invoiceItemData['quantity']*$quantity;
        $rowTotalInclTax =  $invoiceItemData['rowTotalInclTax']/$invoiceItemData['quantity']*$quantity;

        $invoiceItemData['quantity'] = $quantity;
        $invoiceItemData['rowTotalExclTax'] = $rowTotalExclTax;
        $invoiceItemData['rowTotalInclTax'] = $rowTotalInclTax;
        $invoiceItemData['tax'] = $tax;
        $this->assignData($invoiceItem, $invoiceItemData);

        return $invoiceItem;
    }
}
