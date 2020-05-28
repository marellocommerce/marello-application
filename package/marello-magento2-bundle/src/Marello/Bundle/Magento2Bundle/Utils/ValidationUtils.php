<?php

namespace Marello\Bundle\Magento2Bundle\Utils;

use Oro\Bundle\IntegrationBundle\Utils\SecureErrorMessageHelper;

/**
 * @skip
 */
class ValidationUtils
{
    /**
     * Guess validation message prefix based on entity type
     *
     * @param object $entity
     *
     * @return string
     */
    public static function guessValidationMessagePrefix($entity)
    {
        $prefix = 'Validation error: ';
        if (method_exists($entity, 'getOriginId')) {
            $prefix .= sprintf('Magento entity ID %d', $entity->getOriginId());
        }

        return $prefix;
    }

    /**
     * Sanitise error message for secure info
     *
     * @param string
     *
     * @return string
     */
    public static function sanitizeSecureInfo($message)
    {
        /**
         * @todo Replace this on another one
         */
        return SecureErrorMessageHelper::sanitizeSecureInfo($message);
    }
}
