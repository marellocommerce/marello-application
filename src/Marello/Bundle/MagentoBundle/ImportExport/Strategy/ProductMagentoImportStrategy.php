<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

class ProductMagentoImportStrategy extends DefaultMagentoImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        $this->cachedEntities = [];
        $this->cachedInverseSingleRelations = [];
        $this->cachedExistingEntities = [];
        $this->cachedInverseMultipleRelations = [];

        if (!$entity = $this->beforeProcessEntity($entity)) {
            return null;
        }

        if (!$entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'))) {
            return null;
        }

        if (!$entity = $this->afterProcessEntity($entity)) {
            return null;
        }

        return $this->validateAndUpdateContext($entity);
    }
}
