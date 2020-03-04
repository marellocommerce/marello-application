<?php

namespace Marello\Bundle\BankTransferBundle;

use Marello\Bundle\BankTransferBundle\DependencyInjection\MarelloBankTransferExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloBankTransferBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new MarelloBankTransferExtension();
        }

        return $this->extension;
    }
}
