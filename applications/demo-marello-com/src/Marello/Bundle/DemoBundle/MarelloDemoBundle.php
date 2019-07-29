<?php

namespace Marello\Bundle\DemoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloDemoBundle extends Bundle
{
    public function getParent()
    {
        return 'OroUserBundle';
    }
}
