<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
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
     */
    protected $filename;
    
    /**
     * @var string
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);
        
        if ($this->filename) {
            $property = self::ORIGINAL_FILE_NAME_FILTER;
            $value = $this->filename;
        } else {
            $property = EntityReaderById::ID_FILTER;
            $value = $this->id;
        }
        $qb
            ->andWhere('o.' . $property . ' = :' . $property)
            ->setParameter($property, $value ? : -1);

        return $qb;
    }

    /**
     * {@inheritdoc}
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
