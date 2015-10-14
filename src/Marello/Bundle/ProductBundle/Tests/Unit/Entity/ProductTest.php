<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

class ProductTest extends \PHPUnit_Framework_TestCase  {

    /** @var Marello\Bundle\ProductBundle\Entity\Product $entity */
    protected $entity;

    protected function setUp()
    {
        $name         = 'Marello\Bundle\ProductBundle\Entity\Product';
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
            call_user_func_array(array($this->entity, 'set' . ucfirst($property)), array($value));
        }

        $this->assertEquals($expected, call_user_func_array(array($this->entity, 'get' . ucfirst($property)), array()));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $name = 'New Product';
        $sku = 'product123';
        $stockLevel = 100;
        $createdAt    = new \DateTime('now');
        $updatedAt    = new \DateTime('now');
        $owner        = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'name'                => ['name', $name, $name],
            'sku'                 => ['sku', $sku, $sku],
            'stockLevel'          => ['stockLevel', $stockLevel, $stockLevel],
            'createdAt'           => ['createdAt', $createdAt, $createdAt],
            'updatedAt'           => ['updatedAt', $updatedAt, $updatedAt],
            'owner'               => ['owner', $owner, $owner],
            'organization'        => ['organization', $organization, $organization]
        ];
    }
}