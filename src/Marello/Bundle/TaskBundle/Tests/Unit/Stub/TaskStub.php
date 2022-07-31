<?php

namespace Marello\Bundle\TaskBundle\Tests\Unit\Stub;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\TaskBundle\Entity\Task;

class TaskStub extends Task
{
    protected $type;

    public function getType()
    {
        return $this->type;
    }

    public function setType(AbstractEnumValue $enum = null)
    {
        $this->type = $enum;

        return $this;
    }
}
