<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Connector;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class OroCommerceTaxJurisdictionConnector extends AbstractOroCommerceConnector implements TwoWaySyncConnectorInterface
{
    const TYPE = 'tax_jurisdiction';
    const IMPORT_JOB = 'orocommerce_taxjurisdiction_import';
    const EXPORT_JOB = 'orocommerce_taxjurisdiction_export';
    
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
        return 'marello.orocommerce.connector.tax_jurisdiction.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return TaxJurisdiction::class;
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
