<?php

namespace Marello\Bundle\MageBridgeBundle\Controller;

use FOS\RestBundle\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Integration;
use Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner\MagentoResourceOwner;

class IntegrationController extends Controller
{
    /**
     * @Route("/authenticate/{id}", requirements={"id"="\d+"}, name="marello_magento_integration_authenticate")
     * @AclAncestor("marello_integration_update")
     * @param Integration $integration
     * @param Request $request
     * @return Response A Response instance
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

            $status = Codes::HTTP_FOUND;
            $response = [
                'successful' => true,
                'message' => $this->get('translator')->trans(
                    'marello.magebridge.magento.integration.complete_authentication',
                    ['%url%' => $authorizeUrl]
                )
            ];
        } catch (\Exception $e) {
            $status = Codes::HTTP_INTERNAL_SERVER_ERROR;
            $response = [
                'successful' => true,
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($response, $status);
    }

    /**
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

        try {
            $integration = $request->getSession()->get('integration');

            /** @var MagentoResourceOwner $resourceOwner */
            $magentoResourceOwner = $this->getMagentoResourceOwner();

            $magentoResourceOwner
            ->setIntegrationChannel($integration)
            ->setSession($request->getSession())
            ->configureCredentials();

            $accessTokens = $magentoResourceOwner->getAccessToken($request, $this->getCallBackUrl());

            $this->get('marello_magebrdige.action_handler.transport_authentication')
            ->setTokens($accessTokens)
            ->handleAction($integration);
        } catch (\Exception $e) {
            //TODO: log exception
        }

        return $this->redirect($this->getRouterUrl('oro_integration_index'));
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

        return $this->redirect($this->getRouterUrl('oro_integration_index'));
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
