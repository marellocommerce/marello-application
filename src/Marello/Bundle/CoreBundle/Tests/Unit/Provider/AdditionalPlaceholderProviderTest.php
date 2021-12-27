<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\Provider;

use Marello\Bundle\CoreBundle\Model\AdditionalPlaceholderDataInterface;
use Marello\Bundle\CoreBundle\Provider\AdditionalPlaceholderProvider;
use PHPUnit\Framework\TestCase;

class AdditionalPlaceholderProviderTest extends TestCase
{
    /**
     * @var AdditionalPlaceholderProvider
     */
    protected $placeholderProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $additionalPlaceholderDataMock = $this->createMock(AdditionalPlaceholderDataInterface::class);
        $additionalPlaceholderDataMock
            ->expects($this->once())
            ->method('getPlaceholderSections')
            ->willReturn(['somesection']);

        $additionalPlaceholderDataMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('somename');

        $this->placeholderProvider = new AdditionalPlaceholderProvider();
        $this->placeholderProvider->addAdditionalPlaceholderDataProvider($additionalPlaceholderDataMock);
    }

    /**
     * {@inheritdoc}
     */
    public function testRegistrationOfAdditionalPlaceholderProvider()
    {
        self::assertCount(1, $this->placeholderProvider->getPlaceholderProviders());
    }

    public function testHasPlaceholderProvider()
    {
        self::assertTrue(true, $this->placeholderProvider->hasPlaceholderProvider('somesection', 'somename'));
    }

    /**
     * {@inheritdoc}
     */
    public function testGetPlaceHolderPerSection()
    {
        $result = $this->placeholderProvider->getPlaceHolderProvidersBySection('somesection');
        self::assertCount(1, $result);
        self::assertArrayHasKey('somename', $result);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetPlaceHolderPerSectionEmptyResult()
    {
        $result = $this->placeholderProvider->getPlaceHolderProvidersBySection('othersection');
        self::assertCount(0, $result);
        self::assertArrayNotHasKey('somename', $result);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetPlaceHolder()
    {
        $result = $this->placeholderProvider->getPlaceHolderProvider('somesection', 'somename');
        self::assertInstanceOf(AdditionalPlaceholderDataInterface::class, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetPlaceHolderEmptyResult()
    {
        $result = $this->placeholderProvider->getPlaceHolderProvider('othersection', 'noname');
        self::assertCount(0, $result);
        self::assertEmpty($result);
    }
}
