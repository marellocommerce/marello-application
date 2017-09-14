<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory;

use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerInterface;

abstract class AbstractGoogleApiResultFactory implements GoogleApiResultFactoryInterface
{
    const API_NAME = null;
    
    const OK_CODE = 'OK';
    const ZERO_RESULTS_CODE = 'ZERO_RESULTS';
    const OVER_QUERY_LIMIT_CODE = 'OVER_QUERY_LIMIT';
    const INVALID_REQUEST_CODE = 'INVALID_REQUEST';
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RestException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function createResult(RestResponseInterface $response, GoogleApiContextInterface $context)
    {
        /** @var array $data */
        $data = $response->json();
        if (!is_array($data)) {
            throw new \LogicException($data);
        }
        $status = $this->getResponseStatus($data);
        if ($status !== self::OK_CODE) {
            if ($status === self::ZERO_RESULTS_CODE) {
                $message = $this->getZeroResultsErrorMessage($context);
                $resultParams = [
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::WARNING_TYPE,
                    GoogleApiResult::FIELD_ERROR_CODE => self::ZERO_RESULTS_CODE,
                    GoogleApiResult::FIELD_ERROR_MESSAGE =>$message,
                ];
                $this->logger->warning($message);
            } else {
                $message = isset($data['error_message']) ? $data['error_message'] : 'Other error';
                $resultParams = [
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
                    GoogleApiResult::FIELD_ERROR_CODE => $data['status'],
                    GoogleApiResult::FIELD_ERROR_MESSAGE => $message,
                ];
                $this->logger->error($message);
            }
        } else {
            $resultParams = $this->createSuccessResult($data);
        }

        return new GoogleApiResult($resultParams);
    }

    /**
     * @param array $data
     * @return array
     */
    abstract protected function createSuccessResult(array $data);

    /**
     * @param GoogleApiContextInterface $context
     * @return string
     */
    abstract protected function getZeroResultsErrorMessage(GoogleApiContextInterface $context);

    /**
     * @param array $data
     * @return string
     */
    abstract protected function getResponseStatus(array $data);

    /**
     * {@inheritDoc}
     */
    public function createExceptionResult(\Exception $exception)
    {
        $this->logger->error($exception->getMessage());
        
        return new GoogleApiResult([
            GoogleApiResult::FIELD_STATUS => false,
            GoogleApiResult::FIELD_ERROR_TYPE => GoogleApiResult::ERROR_TYPE,
            GoogleApiResult::FIELD_ERROR_CODE => $exception->getCode(),
            GoogleApiResult::FIELD_ERROR_MESSAGE => $exception->getMessage(),
        ]);
    }

    /**
     * @param array  $arr
     * @param string $key
     *
     * @return array
     * @throws \LogicException
     */
    protected function getValueByKeyRecursively(array $arr, $key)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        }

        foreach ($arr as $element) {
            if (is_array($element)) {
                return $this->getValueByKeyRecursively($element, $key);
            }
        }

        throw new \LogicException(sprintf('Google Maps %s API Result format has been changed', self::API_NAME));
    }
}
