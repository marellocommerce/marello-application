<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\IntegrationBundle\Provider\OrderedConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommercePaymentTermConnector extends AbstractOroCommerceConnector implements
    TwoWaySyncConnectorInterface,
    OrderedConnectorInterface
{
    const TYPE = 'payment_term';
    const IMPORT_JOB = 'orocommerce_paymentterm_import';
    const EXPORT_JOB = 'orocommerce_paymentterm_export';
    
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getPaymentTerms();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.orocommerce.connector.payment_term.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return PaymentTerm::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
