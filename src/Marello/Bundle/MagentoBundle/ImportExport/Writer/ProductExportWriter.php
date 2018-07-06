<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Writer;

use Marello\Bundle\MagentoBundle\Entity\Product;

class ProductExportWriter extends AbstractExportWriter
{
    const CONTEXT_POST_PROCESS_KEY = 'postProcessProductExport';
    const PRODUCT_SKU = 'sku';

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $item = reset($items);

        if (!$item) {
            $this->logger->error('Wrong Product data', (array)$item);

            return;
        }

        $this->transport->init($this->getChannel()->getTransport());

        if (!isset($item['productId'])) {
            $this->writeNewItem($item);
        } else {
            $this->writeExistingItem($item);
        }
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        try {
            $productId = $this->transport->createProduct($item);

            if ($productId) {
                $magentoProduct = new Product();
                $magentoProduct->setChannel($this->getChannel())
                    ->setSku($item[self::PRODUCT_SKU])
                    ->setOriginId($productId)
                    ->setName($item['productData']['name'])
                    ->setType($item['type']);

                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$magentoProduct]);

                $this->logger->info(
                    sprintf(
                        'Product with data %s successfully created',
                        $productId
                    )
                );
            } else {
                $this->logger->error(sprintf('Product with data %s was not created', json_encode($item)));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        try {
            $productId = $item['productId'];

            $productData = $this->transport->updateProduct($item);

            if ($productData) {
                $this->stepExecution->getJobExecution()
                    ->getExecutionContext()
                    ->put(self::CONTEXT_POST_PROCESS_KEY, [$productData]);

                $this->logger->info(
                    sprintf(
                        'Product with id %s and data %s successfully updated',
                        $productId,
                        json_encode($productData)
                    )
                );
            } else {
                $this->logger->error(
                    sprintf(
                        'Product with id %s and data %s was not updated',
                        $productId,
                        json_encode($item)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }
}
