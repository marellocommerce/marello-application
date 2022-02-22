<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Connection\Validator\Result;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\UPSBundle\Connection\Validator\Result\UpsConnectionValidatorResult;

class UpsConnectionValidatorResultTest extends TestCase
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var UpsConnectionValidatorResult
     */
    protected $connectionValidationResult;

    protected function setUp(): void
    {
        $this->parameters = [
            'status' => true,
            'error_severity' => 'error_severity',
            'error_message' => 'Wrong Password'
        ];
        $this->connectionValidationResult = new UpsConnectionValidatorResult($this->parameters);
    }

    public function testGetters()
    {
        static::assertEquals($this->parameters['status'], $this->connectionValidationResult->getStatus());
        static::assertEquals(
            $this->parameters['error_severity'],
            $this->connectionValidationResult->getErrorSeverity()
        );
        static::assertEquals($this->parameters['error_message'], $this->connectionValidationResult->getErrorMessage());
    }
}
