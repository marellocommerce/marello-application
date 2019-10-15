<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceTaxRateConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'tax_rate';
    const IMPORT_JOB = 'orocommerce_taxrate_import';
    const EXPORT_JOB = 'orocommerce_taxrate_export';
    
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $obj = new \ArrayObject([]);

        return $obj->getIterator();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.orocommerce.connector.tax_rate.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return TaxRate::class;
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
}
