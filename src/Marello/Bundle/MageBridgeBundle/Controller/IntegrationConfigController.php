<?php

namespace Marello\Bundle\MageBridgeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Integration;
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
        try {
            //save integration
            $request->getSession()->set("integration", $integration);

            /** @var MagentoResourceOwner $resourceOwner */
            $magentoResourceOwner = $this->getMagentoResourceOwner();

            $magentoResourceOwner
                ->setIntegrationChannel($integration)
                ->setSession($request->getSession())
                ->configureCredentials();

            $callBackUrl = $this->getCallBackUrl();

            $authorizeUrl = $magentoResourceOwner->getAuthorizationUrl($callBackUrl);

            echo "
                    <a href='{$authorizeUrl}' target='_blank'>click here</a> 
                    <script>setTimeout(function(){window.location.href=\"{$authorizeUrl}\"},5000);</script>";

//            header("Access-Control-Allow-Origin: *");
//            header("Location: {$authorizeUrl}");

//            sleep(2);
//            header("Refresh:5; url={$authorizeUrl}");


//            return $this->redirect($authorizeUrl);
        } catch (\Exception $e) {
            //TODO: log exception
            print_r($e->__toString());
        }

        exit ();
        return $this;
    }

    /**
     * e.g http://domain.com?oauth_token=06acb886560e15144b886df0a531cccd&oauth_verifier=29deeeec9cfbd824a67790b4573d774d
     *
     * @Route("/callback", requirements={"oauth_token"="\s+","oauth_verifier"="\s+"}, name="marello_magento_integration_callback")
     * @AclAncestor("marello_integration_update")
     */
    public function callbackAction(Request $request)
    {
        $oauthToken = $request->get('oauth_token');
        $oauthVerifier = $request->get('oauth_verifier');

        if ($oauthToken <> $request->getSession()->get('oauth_token')
            or !$request->getSession()->get('integration')) {
            throw new AccessDeniedHttpException("access denied");
        }

        $request->getSession()->set('oauth_verifier', $oauthVerifier);

        //TODO: 3) based on the return back data && session data request final token keys
        //TODO: 4) Save final token on the integration data


        try {
            $integration = $request->getSession()->get('integration');

            /** @var MagentoResourceOwner $resourceOwner */
            $magentoResourceOwner = $this->getMagentoResourceOwner();

            $magentoResourceOwner
                ->setIntegrationChannel($integration)
                ->setSession($request->getSession())
                ->configureCredentials();

            $accessTokens = $magentoResourceOwner->getAccessToken($request, $this->getCallBackUrl());

            $integration->setTokenKey($accessTokens['oauth_token'])
            ->setTokenSecret($accessTokens['oauth_token_secret']);

            $manager = $this->getDoctrine()->getRepository(get_class($integration));
            $manager->persist($integration);
            $manager->flush($integration);

//            var_dump($accessTokens);
//            die(__METHOD__ . '###' . __LINE__);


        } catch (\Exception $e) {
            //TODO: log exception
            var_dump($e->__toString());
            die(__METHOD__ . '###' . __LINE__);
        }

        return $this->redirect("/");

//        var_dump($request->getSession()->get('oauth_token'));
//        var_dump($request->getSession()->get('oauth_token_secret'));
////        var_dump($request->getSession()->get('oauth_callback_confirmed'));
//        var_dump($request->getSession());
//        var_dump($oauthToken);
//        var_dump($oauthVerifier);


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
