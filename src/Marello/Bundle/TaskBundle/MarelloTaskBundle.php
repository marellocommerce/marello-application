<?php

namespace Marello\Bundle\TaskBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloTaskBundle extends Bundle
{
    public function getParent()
    {
        return 'OroTaskBundle';
    }
}
