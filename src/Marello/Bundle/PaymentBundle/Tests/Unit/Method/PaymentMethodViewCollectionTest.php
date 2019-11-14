<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method;

use Marello\Bundle\PaymentBundle\Method\PaymentMethodViewCollection;

/**
* @SuppressWarnings(PHPMD.TooManyMethods)
* @SuppressWarnings(PHPMD.TooManyPublicMethods)
* @SuppressWarnings(PHPMD.ExcessivePublicCount)
*/
class PaymentMethodViewCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return PaymentMethodViewCollection
     */
    private function createCollection()
    {
        return new PaymentMethodViewCollection();
    }

    public function testAddAndGetMethodView()
    {
        $collection = $this->createCollection();

        $methodId = 'someMethodId';

        $view = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $addResult = $collection->addMethodView($methodId, $view);

        $actualView = $collection->getMethodView($methodId);

        $this->assertEquals($collection, $addResult);
        $this->assertNotNull($actualView);
        $this->assertEquals($view, $actualView);
    }

    public function testGetMethodViewWhenNotExists()
    {
        $collection = $this->createCollection();

        $methodId = 'someMethodId';

        $actualView = $collection->getMethodView($methodId);

        $this->assertNull($actualView);
    }

    public function testAddMethodViewWhenAlreadyExists()
    {
        $methodId = 'someMethodId';

        $collection = $this->createCollection();

        $view = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $collection->addMethodView($methodId, $view);

        $view2 = [
            'someField3' => 'someValue4',
            'someField4' => 'someValue4',
            'sortOrder' => 1
        ];

        $addMethodViewResult = $collection->addMethodView($methodId, $view2);

        $actualView = $collection->getMethodView($methodId);

        $this->assertNotNull($actualView);
        $this->assertEquals($view, $actualView);
        $this->assertEquals($collection, $addMethodViewResult);
    }

    public function testHasMethodView()
    {
        $methodId = 'someMethodId';
        $collection = $this->createCollection();

        $collection->addMethodView($methodId, []);

        $this->assertTrue($collection->hasMethodView($methodId));
    }

    public function testHasMethodViewNotExists()
    {
        $collection = $this->createCollection();

        $this->assertFalse($collection->hasMethodView('someMethodId'));
    }

    public function testRemoveMethodView()
    {
        $methodId = 'someMethodId';
        $collection = $this->createCollection();

        $collection->addMethodView($methodId, []);

        $this->assertTrue($collection->hasMethodView($methodId));

        $removeResult = $collection->removeMethodView($methodId);

        $this->assertEquals($collection, $removeResult);
        $this->assertFalse($collection->hasMethodView($methodId));
    }

    public function testRemoveMethodViewWhenNotExists()
    {
        $methodId = 'someMethodId';
        $collection = $this->createCollection();

        $removeResult = $collection->removeMethodView($methodId);

        $this->assertEquals($collection, $removeResult);
        $this->assertFalse($collection->hasMethodView($methodId));
    }

    public function testGetAllMethodsViews()
    {
        $collection = $this->createCollection();

        $this->assertEquals([], $collection->getAllMethodsViews());

        $methodId = 'someMethodId';

        $methodView = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $collection->addMethodView($methodId, $methodView);

        $this->assertEquals([$methodId => $methodView], $collection->getAllMethodsViews());

        $methodId2 = 'someOtherMethodId';

        $methodView2 = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $collection->addMethodView($methodId2, $methodView2);

        $this->assertEquals([$methodId => $methodView, $methodId2 => $methodView2], $collection->getAllMethodsViews());
    }

    public function testToArray()
    {
        $collection = $this->createCollection();

        $methodId = 'someMethodId';

        $methodView = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $collection->addMethodView($methodId, $methodView);

        $methodId2 = 'someOtherMethodId';

        $methodView2 = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $collection->addMethodView($methodId2, $methodView2);

        $this->assertEquals(
            [
                $methodId => $methodView,
                $methodId2 => $methodView2
            ],
            $collection->toArray()
        );
    }

    public function testIsEmpty()
    {
        $collection = $this->createCollection();

        $methodId = 'someMethodId';

        $methodView = [
            'someField1' => 'someValue1',
            'someField2' => 'someValue2',
            'sortOrder' => 1
        ];

        $this->assertTrue($collection->isEmpty());
        
        $collection->addMethodView($methodId, $methodView);

        $this->assertFalse($collection->isEmpty());

        $collection->clear();

        $this->assertTrue($collection->isEmpty());
    }
}
