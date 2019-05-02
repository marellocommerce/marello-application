<?php

namespace Marello\Bundle\ImportExportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloImportExportBundle extends Bundle
{
    public function getParent()
    {
        return 'OroImportExportBundle';
    }
}
