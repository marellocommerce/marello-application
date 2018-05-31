<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

class CategoryConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_category_import';
    const EXPORT_JOB_NAME = 'mage_category_export';
    const TYPE            = 'category';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.category.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::MAGENTO_CATEGORY_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportEntityFQCN()
    {
        return self::MARELLO_CATEGORY_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return self::EXPORT_JOB_NAME;
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
    protected function getConnectorSource()
    {
        return $this->transport->getCategoryList();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
