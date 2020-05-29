<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\OrderExportWriter;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

class PaymentTermNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const PAYMENTTERM_ID = 'orocommerce_paymentterm_id';

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof PaymentTerm && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return isset($data['type']) && $data['type'] === 'paymentterms' && ($type == PaymentTerm::class);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof Order && $object->getOrderReference()) {
            if (in_array(
                $context[AbstractExportWriter::ACTION_FIELD],
                [OrderExportWriter::CANCEL_ACTION, OrderExportWriter::SHIPPED_ACTION]
            )) {
                return [
                    'data' => [
                        'type' => 'orders',
                        'id' => $object->getOrderReference(),
                        'relationships' => [
                            'internal_status' => [
                                'data' => [
                                    'type' => 'orderinternalstatuses',
                                    'id' => $context[AbstractExportWriter::ACTION_FIELD]
                                ]
                            ]
                        ]
                    ]
                ];
            } elseif ($context[AbstractExportWriter::ACTION_FIELD] === OrderExportWriter::PAID_ACTION) {
                return [
                    'data' => [
                        'type' => 'paymentstatuses',
                        'attributes' => [
                            'entityClass' => 'Oro\Bundle\OrderBundle\Entity\Order',
                            'entityIdentifier' => $object->getOrderReference(),
                            'paymentStatus' => OrderNormalizer::PAID_FULLY_STATUS
                        ]
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $this->createPaymentTerm($data);
    }

    /**
     * @param array $data
     * @return PaymentTerm
     */
    public function createPaymentTerm(array $data)
    {
        $label = $this->getProperty($data, 'label');
        preg_match_all('/[0-9]+/', $label, $matches);

        $paymentTerm = new PaymentTerm();
        $paymentTerm
            ->addLabel((new LocalizedFallbackValue())->setString($label))
            ->setCode(str_replace(' ', '_', $label))
            ->setTerm(!empty($matches) && !empty($matches[0]) ? reset($matches[0]) : 30);
        
        return $paymentTerm;
    }
}
