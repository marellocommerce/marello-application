<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Marello\Bundle\Magento2Bundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DefaultMagento2ImportStrategy extends ConfigurableAddOrReplaceStrategy implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Specify channel as identity field
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (!empty($identityValues) && is_a($entityName, IntegrationAwareInterface::class, true)) {
            $identityValues['channel'] = $this->context->getOption('channel');
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * Combine channel with identity values for entity search on local new entities storage
     *
     * @param       $entity
     * @param       $entityClass
     * @param array $searchContext
     *
     * @return array|null
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        if (!empty($searchContext) && is_a($entityClass, IntegrationAwareInterface::class, true)) {
            $searchContext['channel'] = $this->context->getOption('channel');
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }
}
