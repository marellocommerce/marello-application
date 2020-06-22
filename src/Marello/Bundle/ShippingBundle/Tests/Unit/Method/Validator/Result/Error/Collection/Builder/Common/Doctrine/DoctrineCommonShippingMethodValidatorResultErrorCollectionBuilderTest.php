<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Result\Error\Collection\Builder\Common\Doctrine;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Collection\Builder;

class DoctrineCommonShippingMethodValidatorResultErrorCollectionBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testAddErrorAndGetCollection()
    {
        /** @var Error\ShippingMethodValidatorResultErrorInterface $error */
        $error = $this->createMock(Error\ShippingMethodValidatorResultErrorInterface::class);

        $builder = new Builder\Common\Doctrine\DoctrineCommonShippingMethodValidatorResultErrorCollectionBuilder();

        static::assertSame($builder, $builder->addError($error));

        static::assertEquals(
            new Error\Collection\Doctrine\DoctrineShippingMethodValidatorResultErrorCollection([$error]),
            $builder->getCollection()
        );
    }

    public function testCloneAndBuild()
    {
        /** @var Error\ShippingMethodValidatorResultErrorInterface $error */
        $error = $this->createMock(Error\ShippingMethodValidatorResultErrorInterface::class);

        $errorCollection = new Error\Collection\Doctrine\DoctrineShippingMethodValidatorResultErrorCollection([$error]);

        $builder = new Builder\Common\Doctrine\DoctrineCommonShippingMethodValidatorResultErrorCollectionBuilder();

        static::assertSame($builder, $builder->cloneAndBuild($errorCollection));

        static::assertEquals(
            $errorCollection,
            $builder->getCollection()
        );
    }
}
