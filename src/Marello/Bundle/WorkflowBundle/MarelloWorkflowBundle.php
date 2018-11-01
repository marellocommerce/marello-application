<?php

namespace Marello\Bundle\WorkflowBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloWorkflowBundle extends Bundle
{
    public function getParent()
    {
        return 'OroWorkflowBundle';
    }
}
