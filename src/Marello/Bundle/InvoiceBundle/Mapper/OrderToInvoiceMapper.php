<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceType;
use Marello\Bundle\InvoiceBundle\Migrations\Data\ORM\LoadInvoiceTypesData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;

class OrderToInvoiceMapper extends AbstractInvoiceMapper
{
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
        /** @var Order $sourceEntity */
        $invoice = new Invoice();
        $data = $this->getData($sourceEntity, Invoice::class);
        $data['order'] = $sourceEntity;
        $data['items'] = $this->getItems($sourceEntity->getItems());
        if ($data['invoicedAt'] === null) {
            $data['invoicedAt'] = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $data['type'] = $this->getInvoiceType(InvoiceType::INVOICE_TYPE);
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
     * @return PackingSlipItem
     */
    protected function mapItem(OrderItem $orderItem)
    {
        $invoiceItem = new InvoiceItem();
        $invoiceItemData = $this->getData($orderItem, InvoiceItem::class);
        $this->assignData($invoiceItem, $invoiceItemData);

        return $invoiceItem;
    }
}
