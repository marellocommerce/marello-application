<?php

namespace Marello\Bundle\PurchaseOrderBundle;

use Marello\Bundle\PurchaseOrderBundle\DependencyInjection\MarelloPurchaseOrderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloPurchaseOrderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MarelloPurchaseOrderExtension();
    }
}
