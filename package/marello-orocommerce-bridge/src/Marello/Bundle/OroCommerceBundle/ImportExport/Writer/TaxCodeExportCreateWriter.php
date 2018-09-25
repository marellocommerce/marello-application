<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxCode;

class TaxCodeExportCreateWriter extends AbstractExportWriter
{
    const PRODUCT_TAX_CODE_ID = 'orocommerce_producttaxcode_id';
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createProductTaxCode($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'producttaxcodes') {
            $em = $this->registry->getManagerForClass(TaxCode::class);
            $code = $response['data']['attributes']['code'];
            /** @var TaxCode $processedTaxCode */
            $processedTaxCode = $em
                ->getRepository(TaxCode::class)
                ->findOneBy(['code' => $code]);

            if ($processedTaxCode) {
                $data = $processedTaxCode->getData();
                $data[self::PRODUCT_TAX_CODE_ID][$this->channel->getId()] = $response['data']['id'];
                $processedTaxCode->setData($data);

                $em->persist($processedTaxCode);
                $em->flush();
            }
            $this->context->incrementAddCount();
        }
    }
}
