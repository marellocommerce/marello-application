<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 11/05/2018
 * Time: 14:12
 */

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

        $productId = $this->getProduct($item[self::PRODUCT_SKU]);

        $this->transport->init($this->getChannel()->getTransport());

        if (!$productId) {
            $this->writeNewItem($item);
        } else {
            $this->writeExistingItem($productId, $item['productData']);
        }
    }

    /**
     * @param $sku
     * @return mixed
     */
    protected function getProduct($sku)
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManager();

        if ($product = $em->getRepository('MarelloMagentoBundle:Product')->findOneBy(['sku' => $sku])) {
            return $product->getOriginId();
        }

        return false;
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
    protected function writeExistingItem($productId, array $item)
    {
        try {
            $productData = $this->transport->updateProduct($productId, $item);

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
