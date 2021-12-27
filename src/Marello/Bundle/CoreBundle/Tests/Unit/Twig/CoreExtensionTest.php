<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\Twig;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\CoreBundle\Twig\CoreExtension;
use Marello\Bundle\CoreBundle\Provider\AdditionalPlaceholderProvider;

class CoreExtensionTest extends WebTestCase
{
    /**
     * @var AdditionalPlaceholderProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $additionalPlaceholderProvider;

    /**
     * @var CoreExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->additionalPlaceholderProvider = $this->getMockBuilder(AdditionalPlaceholderProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new CoreExtension();
        $this->extension->setPlaceholderProvider($this->additionalPlaceholderProvider);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->extension, $this->additionalPlaceholderProvider);
    }

    /**
     * {@inheritdoc}
     */
    public function testNameIsCorrectlySetAndReturnedFromConstant()
    {
        $this->assertEquals(CoreExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctionsAreRegisteredInExtension()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_get_additional_placeholder_data'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testDataIsBeingReturnedCorrectly()
    {
         $this->additionalPlaceholderProvider
            ->expects($this->once())
            ->method('getPlaceHolderProvidersBySection')
            ->with('customer')
            ->willReturn(['customer' => []]);

        self::assertArrayHasKey('customer', $this->extension->getAdditionalPlaceHolderData('customer'));
    }

    /**
     * {@inheritdoc}
     */
    public function testDataSectionHasNoPlaceholders()
    {
        $this->additionalPlaceholderProvider
            ->expects($this->once())
            ->method('getPlaceHolderProvidersBySection')
            ->with('customer')
            ->willReturn([]);

        self::assertEmpty($this->extension->getAdditionalPlaceHolderData('customer'));
    }
}
