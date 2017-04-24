<?php

namespace Marello\Bundle\ExtendWorkflowBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloExtendWorkflowBundle extends Bundle
{
    public function getParent()
    {
        return 'OroWorkflowBundle';
    }
}
