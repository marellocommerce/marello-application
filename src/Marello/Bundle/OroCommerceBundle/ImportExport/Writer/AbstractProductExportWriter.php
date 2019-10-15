<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxCode;

abstract class AbstractProductExportWriter extends AbstractExportWriter
{
    const PRODUCT_ID_FIELD = 'orocommerce_product_id';
    const UNIT_PRECISION_ID_FIELD = 'orocommerce_unit_precision_id';
    const PRICE_ID_FIELD = 'orocommerce_price_id';
    const INVENTORY_LEVEL_ID_FIELD = 'orocommerce_inventory_level_id';
    const IMAGE_ID_FIELD = 'orocommerce_image_id';
    
    /**
     * @param array $data
     */
    protected function processTaxCode(array $data)
    {
        if (isset($data['data']['relationships']) && isset($data['data']['relationships']['taxCode'])) {
            $taxCode = $data['data']['relationships']['taxCode'];
            if (isset($taxCode['data']) && isset($taxCode['data']['id']) &&
                isset($taxCode['data']['attributes']) && isset($taxCode['data']['attributes']['code'])
            ) {
                $em = $this->registry->getManagerForClass(TaxCode::class);
                $productTaxCodeId = $taxCode['data']['id'];
                $productTaxCodeCode = $taxCode['data']['attributes']['code'];
                /** @var TaxCode $processedTaxCode */
                $processedTaxCode = $em
                    ->getRepository(TaxCode::class)
                    ->findOneBy(['code' => $productTaxCodeCode]);
                if ($processedTaxCode) {
                    $taxCodeData = $processedTaxCode->getData();
                    $taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$this->channel->getId()] =
                        $productTaxCodeId;
                    $processedTaxCode->setData($taxCodeData);

                    $em->persist($processedTaxCode);
                    $em->flush();
                }
            }
        }
    }
}
