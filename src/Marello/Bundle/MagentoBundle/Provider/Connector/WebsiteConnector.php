<?php

namespace Marello\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class WebsiteConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_website_import';
    const TYPE = 'website_dictionary';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getWebsites();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.connector.website.label';
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
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeTransport(ContextInterface $context)
    {
//        $this->contextMediator->resetInitializedTransport();

        parent::initializeTransport($context);
    }
}
