<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 11/05/2018
 * Time: 14:12
 */

namespace Marello\Bundle\MagentoBundle\ImportExport\Writer;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductExportWriter extends AbstractExportWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var Product $entity */
//        $entity = $this->getEntity();

//        $item = reset($items);

//        if (!$item) {
//            $this->logger->error('Wrong Product data', (array)$item);
//
//            return;
//        }

        $this->transport->init($this->getChannel()->getTransport());
        if (empty($itemExistInLocalProduct)) {
            //TODO: CREATE new product in M1
        } else {
            //TODO: UPDATE new product in M1
        }

        parent::write([$entity]);
    }
}
