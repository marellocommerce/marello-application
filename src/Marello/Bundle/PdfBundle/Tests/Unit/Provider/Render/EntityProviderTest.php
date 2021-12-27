<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider\Render;

use Marello\Bundle\PdfBundle\Provider\Render\EntityProvider;
use PHPUnit\Framework\TestCase;

class EntityProviderTest extends TestCase
{
    protected $provider;

    public function setUp(): void
    {
        $this->provider = new EntityProvider();
    }

    public function testSupports()
    {
        $this->assertTrue($this->provider->supports('test value', []));
    }

    public function testGetParams()
    {
        $result = $this->provider->getParams('test value', []);

        $this->assertTrue(is_array($result));
        $this->assertEquals('test value', $result['entity']);
    }
}
