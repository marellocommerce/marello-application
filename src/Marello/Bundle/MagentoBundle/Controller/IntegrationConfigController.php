<?php

namespace Marello\Bundle\MagentoBundle\Controller;

use Guzzle\Http\Exception\CurlException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Marello\Bundle\MagentoBundle\Exception\RuntimeException;
use Marello\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Marello\Bundle\MagentoBundle\Utils\ValidationUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class IntegrationConfigController extends Controller
{
    /**
     * @return JsonResponse
     *
     * @Route("/check", name="marello_magento_integration_check")
     * @AclAncestor("oro_integration_update")
     */
    public function checkAction()
    {
        $handler = $this->get('marello_magento.handler.transport');

        try {
            $response = $handler->getCheckResponse();
        } catch (\Exception $e) {
            $response = $this->logErrorAndGetResponse($e);
        }

        return new JsonResponse($response);
    }

    /**
     * @param \Exception $e
     * @return array
     */
    protected function logErrorAndGetResponse(\Exception $e)
    {
        if ($e instanceof TransportException
            || $e instanceof CurlException
        ) {
            $this->logDebugException($e);

            return $this->createFailResponse(
                $this->get('translator')->trans('marello.magento.controller.not_valid_parameters')
            );
        }

        if ($e instanceof ExtensionRequiredException) {
            $this->logDebugException($e);

            return $this->createFailResponse(
                $this->get('translator')->trans('marello.magento.controller.extension_required')
            );
        }

        if ($e instanceof RuntimeException) {
            $this->logCriticalException($e);

            return $this->createFailResponse(
                $this->get('translator')->trans('marello.magento.controller.transport_error')
            );
        }

        $this->logCriticalException($e);

        return $this->createFailResponse(
            $this->get('translator')->trans('marello.magento.controller.not_valid_parameters')
        );
    }

    /**
     * @param \Exception $exception
     */
    protected function logDebugException(\Exception $exception)
    {
        $message = ValidationUtils::sanitizeSecureInfo($exception->getMessage());
        $this->get('logger')->debug(sprintf('MageCheck error: %s: %s', $exception->getCode(), $message));
    }

    /**
     * @param \Exception $exception
     */
    protected function logCriticalException(\Exception $exception)
    {
        $message = ValidationUtils::sanitizeSecureInfo($exception->getMessage());
        $this->get('logger')->critical(sprintf('MageCheck error: %s: %s', $exception->getCode(), $message));
    }

    /**
     * @param string    $message
     *
     * @return array
     */
    protected function createFailResponse($message)
    {
        $response = [
            'success'      => false,
            'errorMessage' => $message
        ];

        return $response;
    }
}
