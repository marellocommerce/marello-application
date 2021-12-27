<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\UPSBundle\Model\Package;

class PackageTest extends TestCase
{
    use EntityTestCaseTrait;

    const PACKAGING_TYPE_CODE = '02';
    const DIMENSION_CODE = 'IN';
    const DIMENSION_LENGTH = '1';
    const DIMENSION_WIDTH = '2';
    const DIMENSION_HEIGHT = '3';
    const WEIGHT_CODE = 'LB';
    const WEIGHT = '4';

    /**
     * @var Package
     */
    protected $model;

    /**
     * @var  array
     */
    protected $resultArray;

    protected function setUp(): void
    {
        $this->model = new Package();
    }

    protected function tearDown(): void
    {
        unset($this->model);
    }

    public function testAccessors()
    {
        static::assertPropertyAccessors(
            $this->model,
            [
                ['packagingTypeCode', self::PACKAGING_TYPE_CODE],
                ['dimensionCode', self::DIMENSION_CODE],
                ['dimensionLength', self::DIMENSION_LENGTH],
                ['dimensionWidth', self::DIMENSION_WIDTH],
                ['dimensionHeight', self::DIMENSION_HEIGHT],
                ['weightCode', self::WEIGHT_CODE],
                ['weight', self::WEIGHT],
            ]
        );
    }

    public function testToArray()
    {
        $this->initPackage();

        static::assertEquals($this->resultArray, $this->model->toArray());
    }

    public function testToJson()
    {
        $this->initPackage();

        static::assertEquals(json_encode($this->resultArray), $this->model->toJson());
    }

    public function initPackage()
    {
        $this->model
            ->setPackagingTypeCode(self::PACKAGING_TYPE_CODE)
            ->setDimensionCode(self::DIMENSION_CODE)
            ->setDimensionLength(self::DIMENSION_LENGTH)
            ->setDimensionWidth(self::DIMENSION_WIDTH)
            ->setDimensionHeight(self::DIMENSION_HEIGHT)
            ->setWeightCode(self::WEIGHT_CODE)
            ->setWeight(self::WEIGHT);

        $this->resultArray = [
            'PackagingType' => [
                'Code' => self::PACKAGING_TYPE_CODE,
            ],
            'PackageWeight' => [
                'UnitOfMeasurement' => [
                    'Code' => self::WEIGHT_CODE,
                ],
                'Weight' => self::WEIGHT,
            ],
        ];
    }

    public function testCreate()
    {
        $package = Package::create(
            self::WEIGHT_CODE,
            self::WEIGHT
        );

        static::assertEquals(self::WEIGHT_CODE, $package->getWeightCode());
        static::assertEquals(self::WEIGHT, $package->getWeight());
    }
}
