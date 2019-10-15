<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxJurisdictionExportCreateWriter;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;

class TaxJurisdictionNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof TaxJurisdiction && isset($context['channel'])) {
            $channelId = $context['channel'];
            $taxData = $object->getData();
            $data = [
                'data' => [
                    'type' => 'taxjurisdictions',
                    'attributes' => [
                        'code' => $object->getCode(),
                        'description' => $object->getDescription(),
                    ],
                    'relationships' => [
                        'country' => [
                            'data' => [
                                'id' => $object->getCountry()->getIso2Code(),
                                'type' => 'countries'
                            ]
                        ],
                        'zipCodes' => [
                            'data' => []
                        ]
                    ]
                ]
            ];
            if ($region = $object->getRegion()) {
                $data['data']['relationships']['region']['data'] = [
                    'id' => $region->getCombinedCode(),
                    'type' => 'regions'
                ];
            }
            if ($regionText = $object->getRegionText()) {
                $data['data']['attributes']['region'] = $regionText;
            }
            if (count($zipCodes = $object->getZipCodes()) > 0) {
                /** @var ZipCode $zipCode */
                foreach ($zipCodes as $k => $zipCode) {
                    $id = sprintf('zip-code-id-%s', $k);
                    $data['data']['relationships']['zipCodes']['data'][] = [
                        'type' => 'zipcodes',
                        'id' => $id
                    ];
                    $data['included'][] = [
                        'type' => 'zipcodes',
                        'id' => $id,
                        'attributes' => [
                            'zipCode' => $zipCode->getZipCode(),
                            'zipRangeStart' => $zipCode->getZipRangeStart(),
                            'zipRangeEnd' => $zipCode->getZipRangeEnd()
                        ]
                    ];
                }
            }
            if (isset($taxData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]) &&
                isset($taxData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId])
            ) {
                $data['data']['id'] =
                    $taxData[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId];
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
        return ($data instanceof TaxJurisdiction && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
