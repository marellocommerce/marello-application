<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

class TaxRateExportCreateWriter extends AbstractExportWriter
{
    const TAX_ID = 'orocommerce_tax_id';
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createTax($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'taxes') {
            $em = $this->registry->getManagerForClass(TaxRate::class);
            $code = $response['data']['attributes']['code'];
            /** @var TaxRate $processedTaxRate */
            $processedTaxRate = $em
                ->getRepository(TaxRate::class)
                ->findOneBy(['code' => $code]);

            if ($processedTaxRate) {
                $data = $processedTaxRate->getData();
                $data[self::TAX_ID][$this->channel->getId()] = $response['data']['id'];
                $processedTaxRate->setData($data);

                $em->persist($processedTaxRate);
                $em->flush();
            }
            $this->context->incrementAddCount();
        }
    }
}
