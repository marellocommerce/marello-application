<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Marello\Bundle\Magento2Bundle\Entity\IntegrationAwareInterface;

class DefaultMagento2ImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * Specify channel as identity field
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (is_a($entityName, IntegrationAwareInterface::class, true)) {
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
        if (is_a($entityClass, IntegrationAwareInterface::class, true)) {
            $searchContext['channel'] = $this->context->getOption('channel');
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }
}
