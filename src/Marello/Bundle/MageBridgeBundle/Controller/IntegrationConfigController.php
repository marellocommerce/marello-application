<?php

namespace Marello\Bundle\MageBridgeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class IntegrationConfigController extends Controller
{
    /**
     * @Route("/authenticate/{id}", requirements={"id"="\d+"}, name="marello_magento_integration_authenticate")
     * @AclAncestor("marello_integration_update")
     */
    public function requestTokenAction(Integration $integration)
    {
        var_dump($integration->getName());
        die(__METHOD__ . '###'. __LINE__);
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

    /**
     * @return JsonResponse
     *
     * @Route("/rejected", name="marello_magento_integration_rejected_callback")
     * @AclAncestor("marello_integration_update")
     */
    public function rejectedAction()
    {
        //TODO: handle rejected action by magento authentication
    }
}
