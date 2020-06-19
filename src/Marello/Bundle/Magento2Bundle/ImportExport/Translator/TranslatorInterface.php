<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator;

/**
 * Translates entity to DTO that system knows how to normalize
 */
interface TranslatorInterface
{
    /**
     * @param object $entity
     * @param array $context
     * @return object
     */
    public function translate($entity, array $context = []);
}
