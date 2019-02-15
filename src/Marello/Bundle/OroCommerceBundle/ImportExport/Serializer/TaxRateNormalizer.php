<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRateExportCreateWriter;
use Marello\Bundle\TaxBundle\Entity\TaxRate;

class TaxRateNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof TaxRate && isset($context['channel'])) {
            $channelId = $context['channel'];
            $taxData = $object->getData();
            $data = [
                'data' => [
                    'type' => 'taxes',
                    'attributes' => [
                        'code' => $object->getCode(),
                        'rate' => $object->getRate()
                    ],
                ]
            ];
            if (isset($taxData[TaxRateExportCreateWriter::TAX_ID]) &&
                isset($taxData[TaxRateExportCreateWriter::TAX_ID][$channelId])
            ) {
                $data['data']['id'] =
                    $taxData[TaxRateExportCreateWriter::TAX_ID][$channelId];
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
        return ($data instanceof TaxRate && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }
}
