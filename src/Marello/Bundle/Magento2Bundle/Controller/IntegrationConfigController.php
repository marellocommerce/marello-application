<?php

namespace Marello\Bundle\Magento2Bundle\Controller;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IntegrationConfigController extends AbstractController
{
    /**
     * @return JsonResponse
     *
     * @Route(
     *     "/check/{integrationType}/{transportType}",
     *     methods={"POST"},
     *     name="marello_magento2_integration_check",
     *     requirements={"integrationType"=".+","transportType"=".+"}
     * )
     * @AclAncestor("oro_integration_update")
     * @ParamConverter("transportEntity", class="MarelloMagento2Bundle:Magento2Transport", options={"id" = "transportId"})
     * @CsrfProtection()
     */
    public function checkAction(
        Request $request,
        string $integrationType,
        string $transportType,
        Magento2Transport $transportEntity = null
    ) {
        $handler = $this->get('marello_magento2.handler.transport_handler');

        try {
            $response = $handler->getCheckResponse(
                $request,
                $integrationType,
                $transportType,
                $transportEntity
            );

            $response = $this->getUpdateSuccessResponse($response);
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
            || ($e instanceof RuntimeException && $e->isTransportException())
        ) {
            switch ($e->getCode()) {
                case 401:
                    $message = 'marello.magento2.connection_validation.result.authorization_error.message';
                    break;
                case 500:
                    $message = 'marello.magento2.connection_validation.result.connection_error.message';
                    break;
                default:
                    $message = 'marello.magento2.connection_validation.result.not_valid_parameters.message';
                    break;
            }

            $this->logDebugException($e);

            return $this->createFailResponse(
                $this->get('translator')->trans($message)
            );
        }

        $this->logCriticalException($e);

        return $this->createFailResponse(
            $this
                ->get('translator')
                ->trans('marello.magento2.connection_validation.result.not_valid_parameters.message')
        );
    }

    /**
     * @param \Exception $exception
     */
    protected function logDebugException(\Exception $exception)
    {
        $message = $exception->getMessage();
        $this->get('logger')->debug(
            sprintf('Magento 2 check error: %s: %s', $exception->getCode(), $message)
        );
    }

    /**
     * @param \Exception $exception
     */
    protected function logCriticalException(\Exception $exception)
    {
        $message = $exception->getMessage();
        $this->get('logger')->critical(
            sprintf('Magento 2 check error: %s: %s', $exception->getCode(), $message)
        );
    }

    /**
     * @param string $message
     * @return array
     */
    protected function createFailResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }

    /**
     * @param array $responseData
     * @return array
     */
    protected function getUpdateSuccessResponse(array $responseData): array
    {
        $responseData['message'] = $this
            ->get('translator')
            ->trans('marello.magento2.connection_validation.result.success.message');

        return $responseData;
    }
}
