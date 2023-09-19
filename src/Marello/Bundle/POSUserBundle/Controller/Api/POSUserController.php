<?php

namespace Marello\Bundle\POSUserBundle\Controller\Api;

use Marello\Bundle\POSUserBundle\Api\Handler\POSUserRequestActionHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ApiBundle\Controller\RestApiController;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class POSUserController extends RestApiController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * POS User authenticate
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Authenticate POS user by email, username and credentials",
     *     views={"rest_plain","rest_json_api"},
     *     section="marelloposuser",
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
     *          "class"="Marello\Bundle\POSUserBundle\Api\Model\POSUserApi",
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
     */
    public function authenticateAction(Request $request): Response
    {
        return $this->getHandler()->handleAuthenticate($request);
    }

    private function getHandler(): POSUserRequestActionHandler
    {
        return $this->container->get(POSUserRequestActionHandler::class);
    }
}
