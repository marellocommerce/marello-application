<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Result\Error\Collection\Doctrine;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Collection;

class DoctrineShippingMethodValidatorResultErrorCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateCommonBuilder()
    {
        $factory = new Collection\Doctrine\DoctrineShippingMethodValidatorResultErrorCollection();

        static::assertInstanceOf(
            Collection\Builder\Common\Doctrine\DoctrineCommonShippingMethodValidatorResultErrorCollectionBuilder::class,
            $factory->createCommonBuilder()
        );
    }
}
