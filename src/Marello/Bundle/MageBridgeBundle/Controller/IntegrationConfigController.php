<?php

namespace Marello\Bundle\MageBridgeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner\MagentoResourceOwner;

class IntegrationConfigController extends Controller
{

    /**
     * @Route("/authenticate/{id}", requirements={"id"="\d+"}, name="marello_magento_integration_authenticate")
     * @AclAncestor("marello_integration_update")
     * @param Integration $integration
     * @param Request $request
     */
    public function requestTokenAction(Integration $integration, Request $request)
    {
        /** @var MagentoResourceOwner $resourceOwner */
        $magentoResourceOwner = $this->getMagentoResourceOwner();

        $magentoResourceOwner->setIntegrationChannel($integration)->configureCredentials();

        $callBackUrl = $this->getCallBackUrl();
        $rejectedUrl = $this->getRejectedUrl();

        $requestTokens = $magentoResourceOwner->getRequestToken($callBackUrl);



//
//        //todo fix me
//        var_dump($integration->getName());
//        var_dump($callBackUrl);
//        var_dump($rejectedUrl);
//        var_dump($this->getApplicationUrl());
//        var_dump($requestTokens);
        var_dump($request->getSession()->get('oauth_token'));
        var_dump($request->getSession()->get('oauth_token_secret'));
        var_dump($request->getSession()->get('oauth_callback_confirmed'));
        var_dump($request->getSession()->get('timestamp'));


        die(__METHOD__ . '###' . __LINE__);


        //TODO: 1) initiate: get consumerKey, consumerSecret, apiUrl and execute 1st step oauth/initiate
        //TODO: 2) authorize: redirect user to magento admin uri with callback url

//        $handler = $this->get('marello_magebridge.handler.transport');
//
//        try {
//            $response = $handler->getAuthenticateResponse();
//        } catch (\Exception $e) {
//            $response = $this->logErrorAndGetResponse($e);
//        }


//        return $this->redirect('http://symfony.com/doc');
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
        //TODO: 4) Save final token on the integration data
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

    /**
     * @return MagentoResourceOwner
     */
    protected function getMagentoResourceOwner()
    {
        /** @var MagentoResourceOwner $resourceOwner */
        $resourceOwner = $this->get("hwi_oauth.resource_owner.magento");

        return $resourceOwner;
    }

    /**
     * @param $name
     * @param array $parameters
     * @return mixed
     */
    protected function getRouterUrl($name, $parameters = [])
    {
        return $this->get('router')->generate($name, $parameters);
    }

    /**
     * @return mixed
     */
    protected function getCallBackUrl()
    {
        return $this->getApplicationUrl() . $this->getRouterUrl('marello_magento_integration_callback');
    }

    /**
     * @return mixed
     */
    protected function getRejectedUrl()
    {
        return $this->getApplicationUrl() . $this->getRouterUrl('marello_magento_integration_rejected_callback');
    }

    /**
     * @return mixed
     */
    protected function getApplicationUrl()
    {
        return $this->get('oro_config.user')->get("oro_ui.application_url");
    }

}
