<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Unit\Pdf\Table;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Entity\InvoiceItem;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PdfBundle\Lib\View\Table;
use Marello\Bundle\InvoiceBundle\Pdf\Table\InvoiceTableProvider;
use Marello\Bundle\PdfBundle\Provider\TableSizeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class InvoiceTableProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports($entity, $supports)
    {
        /** @var TableSizeProvider|\PHPUnit\Framework\MockObject\MockObject $tableSizesProvider */
        $tableSizesProvider = $this->createMock(TableSizeProvider::class);

        $provider = new InvoiceTableProvider($tableSizesProvider);

        $this->assertEquals($supports, $provider->supports($entity));
    }

    public function supportsProvider()
    {
        return [
            'supported' => [
                'entity' => new Invoice(),
                'supports' => true,
            ],
            'not supported' => [
                'entity' => new Order(),
                'supports' => false,
            ],
        ];
    }

    /**
     * @param $itemCount
     * @param $productNameLength
     * @param $maxHeight
     * @param $maxTextWidth
     * @param $firstPageInfoHeight
     * @param $lastPageInfoHeight
     * @param $tableCount
     * @param $firstItemHeight
     *
     * @dataProvider getTablesProvider
     */
    public function testGetTables(
        $itemCount,
        $productNameLength,
        $maxHeight,
        $maxTextWidth,
        $firstPageInfoHeight,
        $lastPageInfoHeight,
        $tableCount,
        $firstItemHeight
    ) {
        $country = $this->getEntity(Country::class, [
            'iso3Code' => 'NLD',
            'name' => 'Netherlands',
        ],
            [
                'NL'
            ]);

        $billingAddress = $this->getEntity(MarelloAddress::class, [
            'street' => 'Billing street 1',
            'city' => 'Billing city',
            'postalCode' => '12345',
            'country' => $country,
            'firstName' => 'Billing First',
            'lastName' => 'Billing Last',
        ]);

        $shippingAddress = $this->getEntity(MarelloAddress::class, [
            'street' => 'Shipping street 1',
            'city' => 'Shipping city',
            'postalCode' => '23456',
            'country' => $country,
            'firstName' => 'Shipping First',
            'lastName' => 'Shipping Last',
        ]);

        $customerAddress = $this->getEntity(MarelloAddress::class, [
            'street' => 'Customer street 1',
            'city' => 'Customer city',
            'postalCode' => '34567',
            'country' => $country,
            'firstName' => 'Customer First',
            'lastName' => 'Customer Last',
        ]);

        $customer = $this->getEntity(Customer::class, [
            'addresses' => [
                $customerAddress,
            ],
            'shippingAddress' => $customerAddress,
            'primaryAddress' => $customerAddress,
            'firstName' => 'Customer First',
            'lastName' => 'Customer Last',
            'email' => 'customer@example.com',
        ]);

        $salesChannel = $this->getEntity(SalesChannel::class, [
            'name' => 'Test Sales Channel',
            'code' => 'test',
        ]);

        $invoice = $this->getEntity(Invoice::class, [
            'invoiceNumber' => '001',
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'invoicedAt' => new \DateTime('2019-01-01 12:34:56'),
            'invoiceDueDate' => new \DateTime('2019-01-02 23:45:01'),
            'paymentMethod' => 'payment method',
            'shippingMethod' => 'shipping method',
            'shippingMethodType' => 'shipping',
            'status' => 'open',
            'customer' => $customer,
            'salesChannel' => $salesChannel,
            'subtotal' => 100.0,
            'totalTax' => 21.0,
            'grandTotal' => 121.0,
            'shippingAmountInclTax' => 10.0,
            'shippingAmountExclTax' => 12.10,
        ]);

        $subtotal = 0.0;
        $totalTax = 0.0;
        foreach ($this->getItems($itemCount, $productNameLength) as $item) {
            $item->setInvoice($invoice);
            $invoice->addItem($item);

            $subtotal += $item->getRowTotalExclTax();
            $totalTax += $item->getTax();
        }

        $invoice
            ->setSubtotal($subtotal)
            ->setTotalTax($totalTax)
            ->setGrandTotal($subtotal + $totalTax)
        ;

        /** @var TableSizeProvider|\PHPUnit\Framework\MockObject\MockObject $tableSizeProvider */
        $tableSizeProvider = $this->createMock(TableSizeProvider::class);
        $tableSizeProvider->expects($this->atLeastOnce())
            ->method('getMaxHeight')
            ->with($salesChannel)
            ->willReturn($maxHeight)
        ;
        /* $tableSizeProvider->expects($this->atLeastOnce())
             ->method('getMaxTextWidth')
             ->with($salesChannel)
             ->willReturn($maxTextWidth)
         ;*/
        $tableSizeProvider->expects($this->atLeastOnce())
            ->method('getFirstPageInfoHeight')
            ->with($salesChannel)
            ->willReturn($firstPageInfoHeight)
        ;
        $tableSizeProvider->expects($this->atLeastOnce())
            ->method('getLastPageInfoHeight')
            ->with($salesChannel)
            ->willReturn($lastPageInfoHeight)
        ;

        $provider = new InvoiceTableProvider($tableSizeProvider);

        $tables = $provider->getTables($invoice);

        $this->assertTrue(is_array($tables));
        $this->assertCount($tableCount, $tables);
        foreach ($tables as $table) {
            $this->assertInstanceOf(Table::class, $table);
            $this->assertLessThanOrEqual($maxHeight, $table->getHeight());
        }
        $firstTable = reset($tables);
        $firstLine = $firstTable->getLines()[0];

        $this->assertEquals($firstItemHeight, $firstLine->getHeight());
    }

    public function getTablesProvider()
    {
        return [
            'single page' => [
                'itemCount' => 2,
                'productNameLength' => 10,
                'maxHeight' => 36,
                'maxTextWidth' => 20,
                'firstPageInfoHeight' => 13,
                'lastPageInfoHeight' => 3,
                'tableCount' => 1,
                'firstItemHeight' => 1,
            ],
            'two pages' => [
                'itemCount' => 40,
                'productNameLength' => 10,
                'maxHeight' => 36,
                'maxTextWidth' => 20,
                'firstPageInfoHeight' => 13,
                'lastPageInfoHeight' => 3,
                'tableCount' => 2,
                'firstItemHeight' => 1,
            ],
            'three pages' => [
                'itemCount' => 80,
                'productNameLength' => 10,
                'maxHeight' => 36,
                'maxTextWidth' => 20,
                'firstPageInfoHeight' => 13,
                'lastPageInfoHeight' => 3,
                'tableCount' => 3,
                'firstItemHeight' => 1,
            ],
            'multiple lines' => [
                'itemCount' => 1,
                'productNameLength' => 30,
                'maxHeight' => 36,
                'maxTextWidth' => 20,
                'firstPageInfoHeight' => 13,
                'lastPageInfoHeight' => 3,
                'tableCount' => 1,
                'firstItemHeight' => 1,
            ],
        ];
    }

    protected function getItems($count = 1, $productNameLength = 10)
    {
        $productNamePattern = $this->getProductNamePattern($productNameLength);

        $items = [];
        for ($i = 1; $i < $count + 1; $i++) {
            $price = 10 * $i;
            $tax = 2.1 * $i;
            $quantity = $i;
            $discountAmount = 1.0 * ($i -1);

            $items[] = $this->getEntity(InvoiceItem::class, [
                'productName' => sprintf($productNamePattern, $i),
                'productSku' => 'prod-'.$i,
                'price' => $price,
                'tax' => $tax,
                'quantity' => $quantity,
                'discountAmount' => $discountAmount,
                'row_total_incl_tax' => ($quantity * $price) - $discountAmount + $tax,
                'row_total_excl_tax' => ($quantity * $price) - $discountAmount,
            ]);
        }

        return $items;
    }

    protected function getProductNamePattern($length)
    {
        $length -= strlen('Product ');

        $blockCount = floor($length / 3);
        $remainder = $length % 3;
        if ($remainder === 0) {
            $remainder = 3;
            $blockCount -= 1;
        }

        return 'Product '.str_repeat('000 ', $blockCount).'%0'.$remainder.'d';
    }
}
