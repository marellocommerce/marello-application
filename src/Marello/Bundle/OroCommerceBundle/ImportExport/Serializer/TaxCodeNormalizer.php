<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportCreateWriter;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class TaxCodeNormalizer extends AbstractNormalizer
{
    const NEW_PRODUCT_TAX_CODE_ID = 'product-tax-code-id-1';
 
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof TaxCode && isset($context['channel'])) {
            $channelId = $context['channel'];
            $taxData = $object->getData();
            $data = [
                'data' => [
                    'type' => 'producttaxcodes',
                    'attributes' => [
                        'code' => $object->getCode(),
                        'description' => $object->getDescription(),
                    ],
                ]
            ];
            if (isset($taxData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                isset($taxData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId])
            ) {
                $data['data']['id'] =
                    $taxData[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId];
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
        return ($data instanceof TaxCode && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
