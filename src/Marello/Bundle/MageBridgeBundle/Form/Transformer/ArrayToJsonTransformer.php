<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 26/04/2018
 * Time: 14:39
 */

namespace Marello\Bundle\MageBridgeBundle\Form\Transformer;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer as BaseArrayToJsonTransformer;

class ArrayToJsonTransformer extends BaseArrayToJsonTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        file_put_contents('/app/app/logs/debug.log', print_r($value, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', print_r(__METHOD__ .'###'. __LINE__, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);

        if (null === $value || [] === $value) {
            return '';
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        file_put_contents('/app/app/logs/debug.log', print_r($value, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', print_r(__METHOD__ .'###'. __LINE__, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', PHP_EOL, FILE_APPEND | LOCK_EX);

        if (null === $value || '' === $value) {
            return [];
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return json_decode($value, true);
    }
}
