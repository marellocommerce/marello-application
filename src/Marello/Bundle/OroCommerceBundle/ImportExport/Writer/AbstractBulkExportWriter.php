<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest\OroCommerceRestTransport;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractBulkExportWriter extends AbstractExportWriter
{
    /**
     * @param array $entities
     * @throws \Exception
     */
    public function write(array $entities)
    {
        $this->transport->init($this->getChannel()->getTransport());
        $this->writeItems($entities);
    }
    
    /**
     * @param array $entities
     */
    abstract protected function writeItems(array $entities);
}
