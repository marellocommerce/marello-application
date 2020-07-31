<?php

namespace Marello\Bundle\Magento2Bundle\Tests\Unit\Transport\Rest\SearchCriteria;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SearchValueNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class SearchValueNormalizerTest extends TestCase
{
    /** @var SearchValueNormalizer */
    private $normalizer;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->normalizer = new SearchValueNormalizer();
    }

    /**
     * @dataProvider supportNormalizationProvider
     *
     * @param mixed $data
     * @param bool $expectedResult
     */
    public function testSupportNormalization($data, bool $expectedResult)
    {
        $this->assertSame($expectedResult, $this->normalizer->supportsNormalization($data));
    }

    /**
     * @return array|array[]
     * @throws \Exception
     */
    public function supportNormalizationProvider(): array
    {
        return [
            'Case 1. Null value permitted' => [
                'data' => null,
                'expectedResult' => true,
            ],
            'Case 2. Int value permitted' => [
                'data' => 1,
                'expectedResult' => true,
            ],
            'Case 3. String value permitted' => [
                'data' => 'string',
                'expectedResult' => true,
            ],
            'Case 4. Float value permitted' => [
                'data' => 1.12,
                'expectedResult' => true,
            ],
            'Case 5. Array value permitted' => [
                'data' => [],
                'expectedResult' => true,
            ],
            'Case 6. Traversable value permitted' => [
                'data' => new ArrayCollection(),
                'expectedResult' => true,
            ],
            'Case 7. DateTime value permitted' => [
                'data' => new \DateTime('now', new \DateTimeZone('UTC')),
                'expectedResult' => true,
            ],
            'Case 8. Object value is not permitted' => [
                'data' => new \StdClass(),
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @dataProvider normalizeProvider
     *
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function testNormalize($data, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->normalizer->normalize($data)
        );
    }

    /**
     * @return array|array[]
     * @throws \Exception
     */
    public function normalizeProvider(): array
    {
        return [
            'Case 1. Normalized null value' => [
                'data' => null,
                'expectedResult' => null,
            ],
            'Case 2. Normalized int value' => [
                'data' => 1,
                'expectedResult' => '1',
            ],
            'Case 3. Normalized string value' => [
                'data' => 'string',
                'expectedResult' => 'string',
            ],
            'Case 4. Normalized float value' => [
                'data' => 1.12,
                'expectedResult' => '1.12',
            ],
            'Case 5. Normalized array value' => [
                'data' => [1, 2, 3],
                'expectedResult' => '1,2,3',
            ],
            'Case 6. Traversable value permitted' => [
                'data' => new ArrayCollection([1, 2, 3]),
                'expectedResult' => '1,2,3',
            ],
            'Case 7. DateTime value permitted' => [
                'data' => new \DateTime('2019-12-31 23:55:00', new \DateTimeZone('UTC')),
                'expectedResult' => '2019-12-31 23:55:00',
            ]
        ];
    }

    public function testTryNormalizeIncorrectData()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->normalizer->normalize(new \StdClass());
    }
}
