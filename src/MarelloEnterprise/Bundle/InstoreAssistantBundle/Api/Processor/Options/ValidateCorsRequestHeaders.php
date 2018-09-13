<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Options;

use Symfony\Component\HttpFoundation\Response;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Component\ChainProcessor\ParameterBagInterface;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Model\Error;

class ValidateCorsRequestHeaders implements ProcessorInterface
{
    /** CorsRequestHeaders $corsRequestHeaders */
    protected $corsRequestHeaders;

    /** @var ParameterBagInterface $requestHeaders */
    private $requestHeaders;

    /**
     * ValidateCorsRequestHeaders constructor.
     * @param CorsRequestHeaders $corsRequestHeaders
     */
    public function __construct(CorsRequestHeaders $corsRequestHeaders)
    {
        $this->corsRequestHeaders = $corsRequestHeaders;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function process(ContextInterface $context)
    {
        /** @var OptionsContext $context */
        $this->setRequestHeaders($context);

        if (!$this->hasRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ORIGIN)) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    sprintf('Request is missing "%s" header', CorsRequestHeaders::REQUEST_HEADER_ORIGIN),
                    Response::HTTP_BAD_REQUEST
                )
            );
            return;
        }

        if (!$this->hasRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRM)) {
            // not a pre-flight request, but actual request, do nothing....
            return;
        }

        if (!in_array(
            $this->getRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRM),
            $this->corsRequestHeaders->getAllowedAccessControlRequestMethods()
        )) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    sprintf(
                        '%s is not a valid value for "%s" header',
                        $this->getRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRM),
                        CorsRequestHeaders::REQUEST_HEADER_ACRM
                    ),
                    Response::HTTP_BAD_REQUEST
                )
            );
            return;
        }

        if ($this->hasRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRH)
            && !$this->isValidAccessControlRequestHeader()
        ) {
            $context->addError(
                Error::createValidationError(
                    Constraint::REQUEST_DATA,
                    sprintf(
                        '%s is not a valid value for "%s" header',
                        $this->getRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRH),
                        CorsRequestHeaders::REQUEST_HEADER_ACRH
                    ),
                    Response::HTTP_BAD_REQUEST
                )
            );
            return;
        }

        $context->set(CorsRequestHeaders::PREFLIGHT_REQUEST, true);
    }

    /**
     * {@inheritdoc}
     * @param $headerKey
     * @return mixed|null
     */
    protected function getRequestHeader($headerKey)
    {
        return $this->requestHeaders->get($headerKey);
    }

    /**
     * {@inheritdoc}
     * @param $headerKey
     * @return bool
     */
    protected function hasRequestHeader($headerKey)
    {
        return $this->requestHeaders->has($headerKey);
    }

    /**
     * {@inheritdoc}
     * @param OptionsContext $context
     */
    private function setRequestHeaders(OptionsContext $context)
    {
        $this->requestHeaders = $context->getRequestHeaders();
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    private function isValidAccessControlRequestHeader()
    {
        $headerValue = $this->getRequestHeader(CorsRequestHeaders::REQUEST_HEADER_ACRH);
        $allowedHeaders = $this->corsRequestHeaders->getNormalizedAllowedAccessControlRequestHeaders();
        if (null === $headerValue) {
            return false;
        }

        if (strpos($headerValue, ',') === false && !in_array($headerValue, $allowedHeaders)) {
            return false;
        }

        $headerValues = explode(',', $headerValue);
        $isValid = false;
        foreach ($headerValues as $value) {
            if (in_array($value, $allowedHeaders)) {
                $isValid = true;
            }
        }

        return $isValid;
    }
}
