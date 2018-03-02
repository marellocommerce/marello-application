<?php

namespace Marello\Bundle\MageBridgeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class IntegrationConfigController extends Controller
{
    /**
     * @return JsonResponse
     *
     * @Route("/authenticate", name="marello_magento_integration_authenticate")
     * @AclAncestor("marello_integration_update")
     */
    public function requestTokenAction()
    {
        //TODO: 1) initiate: get consumerKey, consumerSecret, apiUrl and execute 1st step oauth/initiate
        //TODO: 2) authorize: redirect user to magento admin uri with callback url

//        $handler = $this->get('marello_magebridge.handler.transport');
//
//        try {
//            $response = $handler->getAuthenticateResponse();
//        } catch (\Exception $e) {
//            $response = $this->logErrorAndGetResponse($e);
//        }
//
        return new JsonResponse(["x" => "y"]);
    }

    /**
     * @return JsonResponse
     *
     * @Route("/callback", name="marello_magento_integration_callback")
     * @AclAncestor("marello_integration_update")
     */
    public function callbackAction()
    {
        //TODO: 3) based on the return back data && session data request final token keys
    }
}

