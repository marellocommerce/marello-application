<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Controller\Api;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\ApiBundle\Controller\AbstractRestApiController;
use Oro\Bundle\ApiBundle\Processor\ActionProcessorBagInterface;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Processor\Authenticate\AuthenticationContext;

class InstoreAssistantUserController extends AbstractRestApiController
{
    /**
     * Authenticate a specific user by either an Email or Username and return the API key for the user
     * when successful
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     description="authenticate instore assistant user by email, username and credentials",
     *     views={"rest_plain","rest_json_api"},
     *     section="instoreuserapi",
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
     *          201="Returned when successfully logged in",
     *          401="Returned when the user is not Authorized to make a call to the API",
     *          403="Returned when the user has no permissions to get the entities",
     *          500="Returned when an unexpected error occurs"
     *     }
     * )
     *
     * @return Response
     */
    public function authenticateAction(Request $request)
    {
        // oro made it impossible to actually use the registration of a new action
        // downside for this is not being able to use the action the way it was supposed to work....
        // reason it doesn't work is because it will try and load the definition when it's not defined yet...
        // this is because the order in which the bundles are loaded probably, all the other actions are defined
        // in the same bundle and are being loaded after they are being included in the OroApiExtension.php
        // of the OroApiBundle...
        $processor = $this->getProcessor($request);
        /** @var AuthenticationContext $context */
        $context = $this->getContext($processor, $request);
        $context->setClassName(InstoreUserApi::class);
        $context->setRequestData($request->request->all());
        $processor->process($context);

        return $this->buildResponse($context);
    }
}
