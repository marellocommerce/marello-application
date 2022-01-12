<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\ImportExport\Job;

use Guzzle\Http\Message\Response;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\ProductExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class OroCommerceProductExportJobTest extends AbstractOroCommerceJobTest
{
    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadProductData::class]);
    }

    public function testExportProduct()
    {
        $productBefore = $this->managerRegistry
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->findOneBy(['sku' => 'p1']);
        $this->assertEmpty($productBefore->getData());

        $jobLog = [];
        $response = $this->createMock(Response::class);
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn(201);

        $createProductResponseFile = file_get_contents(__DIR__ . '/../../DataFixtures/data/createProductResponse.json');
        $response
            ->expects(static::once())
            ->method('json')
            ->willReturn(json_decode($createProductResponseFile, true));
        $this->restClient
            ->expects(static::once())
            ->method('post')
            ->willReturn($response);
        $productWithTaxCodeResponseFile =
            file_get_contents(__DIR__ . '/../../DataFixtures/data/getProductWithTaxCodeResponse.json');
        $this->restClient
            ->expects(static::at(1))
            ->method('getJSON')
            ->willReturn(json_decode($productWithTaxCodeResponseFile, true));
        $productInventoryLevelResponseFile =
            file_get_contents(__DIR__ . '/../../DataFixtures/data/getProductInventoryLevelResponse.json');
        $this->restClient
            ->expects(static::at(2))
            ->method('getJSON')
            ->willReturn(json_decode($productInventoryLevelResponseFile, true));

        $this->runImportExportConnectorsJob(
            self::REVERSE_SYNC_PROCESSOR,
            $this->channel,
            OroCommerceProductConnector::TYPE,
            [
                AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                ProductExportCreateReader::SKU_FILTER => $productBefore->getSku(),
            ],
            $jobLog
        );

        $productAfter = $this->managerRegistry
            ->getManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->findOneBy(['sku' => 'p1']);
        $productAfterData = $productAfter->getData();
        $this->assertNotEmpty($productAfterData);
        $this->assertArrayHasKey(ProductExportCreateWriter::PRODUCT_ID_FIELD, $productAfterData);
        $this->assertArrayHasKey(ProductExportCreateWriter::UNIT_PRECISION_ID_FIELD, $productAfterData);
        $this->assertArrayHasKey(ProductExportCreateWriter::INVENTORY_LEVEL_ID_FIELD, $productAfterData);
    }
}
