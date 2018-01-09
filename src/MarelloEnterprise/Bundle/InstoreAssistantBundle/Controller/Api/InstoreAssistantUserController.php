<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\Controller\FOSRestController;

use Oro\Bundle\ApiBundle\Controller\RestApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Request\RestFilterValueAccessor;

class InstoreAssistantUserController extends FOSRestController
{
    /**
     * Retrieve a specific record.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     description="verify instore assistant user by email, username and credentials",
     *     views={"rest_plain","rest_json_api"},
     *     section="instoreusers",
     *     requirements={
     *          {
     *              "name"="username",
     *              "dataType"="string",
     *              "requirement"="[a-zA-Z0-9\-_\.@]+",
     *              "nullable"="true",
     *              "description"="Username of the User"
     *          },
     *          {
     *              "name"="email",
     *              "dataType"="string",
     *              "requirement"="[a-zA-Z0-9\-_\.@]+",
     *              "nullable"="true",
     *              "description"="Email of the User"
     *          },
     *          {
     *              "name"="credentials",
     *              "dataType"="string",
     *              "requirement"="[a-zA-Z0-9\-_\.@]+",
     *              "nullable"="false",
     *              "description"="Users password for the account"
     *          }
     *     },
     *     output={
     *          "class"="MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi",
     *          "fields"={
     *              {
     *                  "name"="apiKey",
     *                  "dataType"="string",
     *                  "description"="API key for logged in Instore User"
     *              }
     *          }
     *     },
     *     statusCodes={
     *          200="Returned when successful",
     *          500="Returned when an unexpected error occurs"
     *     }
     * )
     *
     * @return Response
     */
    public function verifyAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var GetContext $context */
        $context = $this->getContext($processor, $request);
//        $context->setId($request->attributes->get('id'));
//        $context->set('apiKey', $request->attributes->get(''));
//        $context->setFilterValues(new RestFilterValueAccessor($request));
//
//        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * @param Context $context
     *
     * @return Response
     */
    protected function buildResponse(Context $context)
    {
        $view = $this->view($context->getResult());

        $view->setStatusCode($context->getResponseStatusCode() ?: Response::HTTP_OK);
        foreach ($context->getResponseHeaders()->toArray() as $key => $value) {
            $view->setHeader($key, $value);
        }

        // use custom handler because the response data are already normalized
        // and we do not need to additional processing of them
        /** @var ViewHandler $handler */
        $handler = $this->get('fos_rest.view_handler');
        $handler->registerHandler(
            'json',
            function (ViewHandler $viewHandler, View $view, Request $request, $format) {
                $response = $view->getResponse();
                $encoder = new JsonEncode();
                $response->setContent($encoder->encode($view->getData(), $format));
                if (!$response->headers->has('Content-Type')) {
                    $response->headers->set('Content-Type', $request->getMimeType($format));
                }

                return $response;
            }
        );

        return $handler->handle($view);
    }
}
