<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Entity;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class PaymentTermTest extends TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testProperties()
    {
        $entity = new PaymentTerm();
        static::assertPropertyAccessors($entity, [
            ['id', 123],
            ['code', 'test14'],
            ['term', 14],
        ]);

        static::assertPropertyCollections($entity, [
            ['labels', new LocalizedFallbackValue()],
        ]);
    }
}
