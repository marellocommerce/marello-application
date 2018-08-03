<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Connection\Validator;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Marello\Bundle\UPSBundle\Client\Factory\UpsClientFactoryInterface;
use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequestInterface;
use Marello\Bundle\UPSBundle\Connection\Validator\Request\Factory\UpsConnectionValidatorRequestFactoryInterface;
use Marello\Bundle\UPSBundle\Connection\Validator\Result\Factory\UpsConnectionValidatorResultFactoryInterface;
use Marello\Bundle\UPSBundle\Connection\Validator\Result\UpsConnectionValidatorResultInterface;
use Marello\Bundle\UPSBundle\Connection\Validator\UpsConnectionValidator;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Psr\Log\LoggerInterface;

class UpsConnectionValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpsConnectionValidatorRequestFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestFactory;

    /**
     * @var UpsClientFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientFactory;

    /**
     * @var UpsConnectionValidatorResultFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var UpsConnectionValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->requestFactory = $this->createMock(UpsConnectionValidatorRequestFactoryInterface::class);
        $this->clientFactory = $this->createMock(UpsClientFactoryInterface::class);
        $this->resultFactory = $this->createMock(UpsConnectionValidatorResultFactoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->validator = new UpsConnectionValidator(
            $this->requestFactory,
            $this->clientFactory,
            $this->resultFactory,
            $this->logger
        );
    }

    public function testValidateConnectionByUpsSettings()
    {
        $transport= new UPSSettings();

        $request = $this->createMock(UpsClientRequestInterface::class);
        $client = $this->createMock(RestClientInterface::class);
        $response = $this->createMock(RestResponseInterface::class);
        $result = $this->createMock(UpsConnectionValidatorResultInterface::class);

        $this->requestFactory->expects(static::once())
            ->method('createByTransport')
            ->with($transport)
            ->willReturn($request);

        $this->clientFactory->expects(static::once())
            ->method('createUpsClient')
            ->willReturn($client);

        $client->expects(static::once())
            ->method('post')
            ->willReturn($response);

        $this->resultFactory->expects(static::once())
            ->method('createResultByUpsClientResponse')
            ->willReturn($result);

        static::assertSame($result, $this->validator->validateConnectionByUpsSettings($transport));
    }

    public function testValidateConnectionByUpsSettingsServerError()
    {
        $transport= new UPSSettings();

        $request = $this->createMock(UpsClientRequestInterface::class);
        $client = $this->createMock(RestClientInterface::class);
        $result = $this->createMock(UpsConnectionValidatorResultInterface::class);

        $this->requestFactory->expects(static::once())
            ->method('createByTransport')
            ->with($transport)
            ->willReturn($request);

        $this->clientFactory->expects(static::once())
            ->method('createUpsClient')
            ->willReturn($client);

        $client->expects(static::once())
            ->method('post')
            ->willThrowException(new RestException);

        $this->resultFactory->expects(static::once())
            ->method('createExceptionResult')
            ->willReturn($result);

        static::assertSame($result, $this->validator->validateConnectionByUpsSettings($transport));
    }
}
