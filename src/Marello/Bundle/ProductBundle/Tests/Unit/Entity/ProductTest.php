<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductTest extends \PHPUnit_Framework_TestCase
{

    /** @var Product $entity */
    protected $entity;

    protected function setUp()
    {
        $name         = Product::class;
        $this->entity = new $name();
    }

    public function tearDown()
    {
        unset($this->entity);
    }

    /**
     * @dataProvider  getSetDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($property, $value = null, $expected = null)
    {
        if ($value !== null) {
            call_user_func_array([$this->entity, 'set' . ucfirst($property)], [$value]);
        }

        $this->assertEquals($expected, call_user_func_array([$this->entity, 'get' . ucfirst($property)], []));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $name         = 'New Product';
        $sku          = 'product123';
        $createdAt    = new \DateTime('now');
        $updatedAt    = new \DateTime('now');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'name'         => ['name', $name, $name],
            'sku'          => ['sku', $sku, $sku],
            'createdAt'    => ['createdAt', $createdAt, $createdAt],
            'updatedAt'    => ['updatedAt', $updatedAt, $updatedAt],
            'organization' => ['organization', $organization, $organization],
        ];
    }
}
