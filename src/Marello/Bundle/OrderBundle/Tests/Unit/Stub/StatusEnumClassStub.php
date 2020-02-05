<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Stub;

/**
 * Represents an enum class for ItemStatuses which don't have a 'physical' representation
 *
 * Class StatusEnumClassStub
 * @package Marello\Bundle\OrderBundle\Tests\Unit\Stub
 */
class StatusEnumClassStub
{
    public function getId($id = null)
    {
        return $id;
    }
}
