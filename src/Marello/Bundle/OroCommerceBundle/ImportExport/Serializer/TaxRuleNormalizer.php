<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxJurisdictionExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRateExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRuleExportCreateWriter;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

class TaxRuleNormalizer extends AbstractNormalizer
{
    /**
     * @var NormalizerInterface
     */
    private $taxJurisdictionNormalizer;

    /**
     * @param Registry $registry
     * @param NormalizerInterface $taxJurisdictionNormalizer
     */
    public function __construct(Registry $registry, NormalizerInterface $taxJurisdictionNormalizer)
    {
        parent::__construct($registry);
        $this->taxJurisdictionNormalizer = $taxJurisdictionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof TaxRule && isset($context['channel'])) {
            $channelId = $context['channel'];
            $productTaxCodeId = null;
            $taxJurisdictionId = null;
            $taxId = null;
            /** @var OroCommerceSettings $transport */
            $transport = $this->getIntegrationChannel($context['channel'])->getTransport();
            $taxData = $object->getData();
            $customerTaxCodeId = (string)$transport->getCustomerTaxCode();
            $taxCode = $object->getTaxCode();
            $taxCodeData = $taxCode->getData();
            if (isset($taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                isset($taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId])) {
                $productTaxCodeId = $taxCodeData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId];
            }
            $taxRate = $object->getTaxRate();
            $taxRateData = $taxRate->getData();
            if (isset($taxRateData[TaxRateExportCreateWriter::TAX_ID]) &&
                isset($taxRateData[TaxRateExportCreateWriter::TAX_ID][$channelId])) {
                $taxId = $taxRateData[TaxRateExportCreateWriter::TAX_ID][$channelId];
            }
            $taxJurisdiction = $object->getTaxJurisdiction();
            $taxJurisdictionData = $taxJurisdiction->getData();
            if (isset($taxJurisdictionData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]) &&
                isset($taxJurisdictionData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId])) {
                $taxJurisdictionId =
                    $taxJurisdictionData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId];
            }

            $data = [
                'data' => [
                    'type' => 'taxrules',
                    'relationships' => [
                        'customerTaxCode' => [
                            'data' => [
                                'type' => 'customertaxcodes',
                                'id' => $customerTaxCodeId
                            ]
                        ],
                        'productTaxCode' => [
                            'data' => [
                                'type' => 'producttaxcodes',
                                'id' => $productTaxCodeId ? : TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID
                            ]
                        ],
                        'tax' => [
                            'data' => [
                                'type' => 'taxes',
                                'id' => $taxId ? : 'tax-id-1'
                            ]
                        ],
                        'taxJurisdiction' => [
                            'data' => [
                                'type' => 'taxjurisdictions',
                                'id' => $taxJurisdictionId ? : 'tax-jurisdiction-id-1'
                            ]
                        ],
                    ],
                ]
            ];
            if (!$productTaxCodeId || !$taxId || !$taxJurisdictionId) {
                $data['included'] = [];
                if (!$productTaxCodeId) {
                    $data['included'][] = [
                        'type' => 'producttaxcodes',
                        'id' => TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID,
                        'attributes' => [
                            'code' => $taxCode->getCode(),
                            'description' => $taxCode->getDescription(),
                        ],
                    ];
                }
                if (!$taxId) {
                    $data['included'][] = [
                        'type' => 'taxes',
                        'id' => 'tax-id-1',
                        'attributes' => [
                            'code' => $taxRate->getCode(),
                            'rate' => $taxRate->getRate(),
                        ],
                    ];
                }
                if (!$taxJurisdictionId) {
                    $normalizedJurisdiction = $this->taxJurisdictionNormalizer->normalize(
                        $taxJurisdiction,
                        null,
                        $context
                    );
                    $normalizedJurisdictionData = $normalizedJurisdiction['data'];
                    $normalizedJurisdictionData['id'] = 'tax-jurisdiction-id-1';
                    if (isset($normalizedJurisdiction['included'])) {
                        foreach ($normalizedJurisdiction['included'] as $included) {
                            if ($included['type'] === 'zipcodes') {
                                $included['relationships']['taxJurisdiction']['data'] = [
                                    'type' => 'taxjurisdictions',
                                    'id' => 'tax-jurisdiction-id-1'
                                ];
                            }
                            $data['included'][] = $included;
                        }
                    }
                    $data['included'][] = $normalizedJurisdictionData;
                }
            }

            if (isset($taxData[TaxRuleExportCreateWriter::TAX_RULE_ID]) &&
                isset($taxData[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId])
            ) {
                $data['data']['id'] =
                    $taxData[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId];
            }

            return $data;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof TaxRule && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
