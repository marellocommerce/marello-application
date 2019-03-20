<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductImageExportCreateReader extends EntityReader
{
    const ORIGINAL_FILE_NAME_FILTER = 'originalFilename';
    
    /**
     * @var string
     * @deprecated will be removed in 2.0
     */
    protected $filename;
    
    /**
     * @var string
     * @deprecated will be removed in 2.0
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $property = EntityReaderById::ID_FILTER;
        $value = $this->getParametersFromContext($property);

        if ($this->filename) {
            $property = self::ORIGINAL_FILE_NAME_FILTER;
            $value = $this->getParametersFromContext($property);
        }

        $qb
            ->andWhere('o.' . $property . ' = :' . $property)
            ->setParameter($property, $value);

        return $qb;
    }

    /**
     * {@inheritdoc}
     * @param string $parameter
     * @return string|null
     */
    protected function getParametersFromContext($parameter)
    {
        $context = $this->getContext();
        if ($context->getOption('entityName') === File::class) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::CREATE_ACTION
                && $context->hasOption($parameter)
            ) {
                return $context->getOption($parameter);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @deprecated will be removed in 2.0 in favour of the parent action
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption('entityName') === File::class &&
            $context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::CREATE_ACTION) {
            $this->filename = $context->getOption(self::ORIGINAL_FILE_NAME_FILTER);
            $this->id = $context->getOption(EntityReaderById::ID_FILTER);
        }

        parent::initializeFromContext($context);
    }
}
