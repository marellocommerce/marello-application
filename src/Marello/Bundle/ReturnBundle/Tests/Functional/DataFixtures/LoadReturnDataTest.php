<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderDataTest;

class LoadReturnDataTest extends LoadReturnData
{
    public function getDependencies()
    {
        return [
            LoadOrderDataTest::class,
        ];
    }
}
