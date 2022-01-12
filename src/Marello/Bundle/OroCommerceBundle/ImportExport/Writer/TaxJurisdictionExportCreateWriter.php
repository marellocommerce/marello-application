<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class TaxJurisdictionExportCreateWriter extends AbstractExportWriter
{
    const TAX_JURISDICTION_ID = 'orocommerce_tax_jurisdiction_id';
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->createTaxJurisdiction($data);

        if (isset($response['data']) && isset($response['data']['type']) && isset($response['data']['id']) &&
            $response['data']['type'] === 'taxjurisdictions') {
            $em = $this->registry->getManagerForClass(TaxJurisdiction::class);
            $code = $response['data']['attributes']['code'];
            /** @var TaxJurisdiction $processedTaxJurisdiction */
            $processedTaxJurisdiction = $em
                ->getRepository(TaxJurisdiction::class)
                ->findOneBy(['code' => $code]);

            if ($processedTaxJurisdiction) {
                $data = $processedTaxJurisdiction->getData();
                $data[self::TAX_JURISDICTION_ID][$this->channel->getId()] = $response['data']['id'];
                $processedTaxJurisdiction->setData($data);

                $em->persist($processedTaxJurisdiction);
                $em->flush();
            }
            $this->context->incrementAddCount();
        }
    }
}
