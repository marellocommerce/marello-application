<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Cache;

use Marello\Bundle\MagentoBundle\Cache\WsdlCacheClearer;

class WsdlCacheClearerTest extends \PHPUnit_Framework_TestCase
{
    public function testClear()
    {
        $wsdlManager = $this->getMockBuilder('Marello\Bundle\MagentoBundle\Service\WsdlManager')
            ->disableOriginalConstructor()
            ->getMock();
        $wsdlManager->expects($this->once())
            ->method('clearAllWsdlCaches');

        $clearer = new WsdlCacheClearer($wsdlManager);
        $clearer->clear('.');
    }
}
