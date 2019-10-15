<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest\OroCommerceRestTransport;
use Marello\Bundle\TaxBundle\Entity\TaxRule;

class TaxRuleExportCreateWriter extends AbstractExportWriter
{
    const TAX_RULE_ID = 'orocommerce_tax_rule_id';

    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $action = $this->context->getOption(AbstractExportWriter::ACTION_FIELD);
        if ($action === AbstractExportWriter::CREATE_ACTION) {
            $response = $this->transport->createTaxRule($data);
        } elseif ($action === AbstractExportWriter::UPDATE_ACTION) {
            $response = $this->transport->updateTaxRule($data);
        } else {
            return;
        }

        if (isset($response['data'])&& isset($response['included']) && isset($response['data']['type']) &&
            isset($response['data']['id']) && $response['data']['type'] === OroCommerceRestTransport::TAXRULES_ALIAS) {
            $em = $this->registry->getManagerForClass(TaxRule::class);
            $taxCode = null;
            $taxRate = null;
            $taxJurisdiction = null;
            foreach ($response['included'] as $included) {
                if ($included['type'] === OroCommerceRestTransport::PRODUCTTAXCODES_ALIAS) {
                    $taxCode = $included['attributes']['code'];
                }
                if ($included['type'] === OroCommerceRestTransport::TAXES_ALIAS) {
                    $taxRate = $included['attributes']['code'];
                }
                if ($included['type'] === OroCommerceRestTransport::TAXJURISDICTIONS_ALIAS) {
                    $taxJurisdiction = $included['attributes']['code'];
                }
            }
            /** @var TaxRule $processedTaxRule */
            $processedTaxRule = $em
                ->getRepository(TaxRule::class)
                ->findOneByCodes($taxCode, $taxRate, $taxJurisdiction);
            if ($processedTaxRule) {
                $data = $processedTaxRule->getData();
                $data[self::TAX_RULE_ID][$this->channel->getId()] = $response['data']['id'];
                $processedTaxRule->setData($data);

                $em->persist($processedTaxRule);

                $processedTaxCode = $processedTaxRule->getTaxCode();
                $taxCodeData = $processedTaxCode->getData();
                $taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$this->channel->getId()] =
                    $response['data']['relationships']['productTaxCode']['data']['id'];
                $processedTaxCode->setData($taxCodeData);

                $em->persist($processedTaxCode);

                $processedTaxRate = $processedTaxRule->getTaxRate();
                $taxRateData = $processedTaxRate->getData();
                $taxRateData[TaxRateExportCreateWriter::TAX_ID][$this->channel->getId()] =
                    $response['data']['relationships']['tax']['data']['id'];
                $processedTaxRate->setData($taxRateData);

                $em->persist($processedTaxRate);

                $processedTaxJurisdiction = $processedTaxRule->getTaxJurisdiction();
                $taxJurisdictionData = $processedTaxJurisdiction->getData();
                $taxJurisdictionData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$this->channel->getId()] =
                    $response['data']['relationships']['taxJurisdiction']['data']['id'];
                $processedTaxJurisdiction->setData($taxJurisdictionData);

                $em->persist($processedTaxJurisdiction);
                $em->flush();
            }
            if ($action === AbstractExportWriter::CREATE_ACTION) {
                $this->context->incrementAddCount();
            } elseif ($action === AbstractExportWriter::UPDATE_ACTION) {
                $this->context->incrementUpdateCount();
            }
        }
    }
}
