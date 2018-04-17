<?php

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use Buzz\Exception\ClientException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use Oro\Bundle\MagentoBundle\Entity\IntegrationEntityTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Request;

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner as BaseGenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class MagentoResourceOwner extends BaseGenericOAuth1ResourceOwner
{
    use IntegrationChannelTrait;
    use SessionTrait;

    const PRODUCT_API_RESOURCE = '/api/rest/products';
    const INVENTORY_API_RESOURCE = '/api/rest/stockitems';

    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'user.username',
        'nickname' => 'user.username',
        'realname' => 'user.display_name',
        'profilepicture' => 'user.avatar',
    );


    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'request_token_url' => 'https://magento_domain.com/oauth/initiate',
            'authorization_url' => 'https://magento_domain.com/admin/oauth_authorize',
            'access_token_url' => 'https://magento_domain.com/oauth/token',
            'infos_url' => 'https://magento_domain.com/',
            'client_id' => 'magento_consumer_key',
            'client_secret' => 'magento_consumer_secret',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestToken($redirectUri, array $extraParameters = array())
    {
        $requestTokens = parent::getRequestToken($redirectUri, $extraParameters);

        return $this->setResponseSession($requestTokens);
    }

    /**
     * Performs an HTTP request.
     *
     * @param string $url The url to fetch
     * @param string|array $content The content of the request
     * @param array $headers The headers of the request
     * @param string $method The HTTP method to use
     *
     * @return HttpResponse The response content
     */

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, $parameters = array(), $headers = array(), $method = null)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $key . '="' . rawurlencode($value) . '"';
        }

        if (!$this->options['realm']) {
            array_unshift($parameters, 'realm="' . rawurlencode($this->options['realm']) . '"');
        }

        $headers[] = 'Authorization: OAuth ' . implode(', ', $parameters);

        if (null === $method) {
            $method = null === $content || '' === $content ? HttpRequestInterface::METHOD_GET : HttpRequestInterface::METHOD_POST;
        }

        $request = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $contentLength = 0;
        if (is_string($content)) {
            $contentLength = strlen($content);
        } elseif (is_array($content)) {
            $contentLength = strlen(implode('', $content));
        }

        $headers = array_merge(
            array(
                'User-Agent: Marello (https://www.marello.com/)',
                'Content-Length: ' . $contentLength,
            ),
            $headers
        );

        $request->setHeaders($headers);
        $request->setContent($content);

        try {
            $this->httpClient->send($request, $response, [CURLOPT_FOLLOWLOCATION => false]);
        } catch (ClientException $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * e.g /api/rest/products?page=2&limit=20
     * Retrieve products
     * @param int $page
     * @param int $limit
     * @return \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface
     */
    public function getProducts($page = 1, $limit = 10)
    {
        $url = $this->options['product_api_resource'] . "?page={$page}&limit={$limit}";
        $response = $this->processTransport($url);

        return $response;
    }

    /**
     * @param $content
     * @return $this
     */
    public function createProduct($content)
    {
        $url = $this->options['product_api_resource'];
        $response = $this->processTransport($url, $content, [], HttpRequestInterface::METHOD_POST);
        //TODO: process response

        return $this;
    }

    public function getWebsites($content) //product_id
    {
//        $response = $this->processTransport($this->options['infos_url'] .'/api/rest/products/2130/store/2', json_encode(['price' => 20.99]), [],HttpRequestInterface::METHOD_PUT);
        $response = $this->processTransport($this->options['infos_url'] .'/api/rest/stores', json_encode(['price' => 20.99]), [],HttpRequestInterface::METHOD_PUT);

        die();
    }

    /**
     * @param $apiResourceUrl
     * @param null $bodyContent
     * @param array $extraParameters
     * @param string $method
     * @return \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface
     */
    public function processTransport(
        $apiResourceUrl,
        $bodyContent = null,
        $extraParameters = [],
        $method = HttpRequestInterface::METHOD_GET
    ) {
        $accessToken = [
            'oauth_token' => $this->getIntegrationChannel()->getTokenKey(),
            'oauth_token_secret' => $this->getIntegrationChannel()->getTokenSecret()
        ];
        $extraParameters = !empty($extraParameters)
                            ? $extraParameters : [];
        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ), $extraParameters);

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            $method,
            $apiResourceUrl,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->httpRequest(
            $apiResourceUrl,
            $bodyContent,
            $parameters,
            ['Content-Type' => 'application/json', 'Accept' => '*/*'],
            $method
        )->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], array('oauth_token' => $token['oauth_token']));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        try {
            $requestToken = $request->query->get('oauth_token');
            if (null === $requestToken) {
                throw new \RuntimeException('No request token found in the storage.');
            }
        } catch (\InvalidArgumentException $e) {
            throw new AuthenticationException('Given token is not valid.');
        }

        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $request->query->get('oauth_token'),
            'oauth_verifier' => $request->query->get('oauth_verifier'),
        ), $extraParameters);

        $url = $this->options['access_token_url'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_POST,
            $url,
            $parameters,
            $this->options['client_secret'],
            $this->getSession()->get('oauth_token_secret'),
            $this->options['signature_method']
        );

        $response = $this->doGetTokenRequest($url, $parameters);
        $response = $this->getResponseContent($response);

        if (isset($response['oauth_problem'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (!isset($response['oauth_token']) || !isset($response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        return $this->setResponseSession($response);
    }

    /**
     * @param $response
     * @return mixed
     */
    private function setResponseSession($response)
    {
        foreach ($response as $key => $value) {
            $this->getSession()->set($key, $value);
        }

        return $response;
    }
}
