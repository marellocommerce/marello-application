<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceTaxCodeConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'tax_code';
    const IMPORT_JOB = 'orocommerce_taxcode_import';
    const EXPORT_JOB = 'orocommerce_taxcode_export';
    
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
        return 'marello.orocommerce.connector.tax_code.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return TaxCode::class;
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
