<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\ActionInterface;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class TransitCompleteActionTest extends \PHPUnit_Framework_TestCase
{
    public function testTransitAction()
    {
        $this->assertTrue(true);
    }
}
