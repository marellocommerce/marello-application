<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class OrderExportWriter extends AbstractExportWriter
{
    const CANCEL_ACTION = 'cancelled';
    const PAID_ACTION = 'paid';
    const SHIPPED_ACTION = 'shipped';

    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $action = $this->context->getOption(self::ACTION_FIELD);
        $type = 'orders';
        if (in_array($action, [self::CANCEL_ACTION, self::SHIPPED_ACTION])) {
            $response = $this->transport->updateOrder($data);
        } elseif ($action = self::PAID_ACTION) {
            $response = $this->transport->createPaymentStatus($data);
            $type = 'paymentstatuses';
        }
        if (isset($response['data']) && isset($response['data']['type']) &&
            $response['data']['type'] === $type
        ) {
            $this->context->incrementUpdateCount();
        }
    }
}
