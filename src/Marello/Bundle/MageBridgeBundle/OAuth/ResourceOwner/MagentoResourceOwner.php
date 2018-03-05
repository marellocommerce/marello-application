<?php

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use Buzz\Exception\ClientException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Request;

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner as BaseGenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class MagentoResourceOwner extends BaseGenericOAuth1ResourceOwner
{
//    use ConfigurableCredentialsTrait;
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
            'request_token_url' => 'https://m1demo.test-madia.nl/oauth/initiate',
            'authorization_url' => 'https://m1demo.test-madia.nl/admin/oauth_authorize',
            'access_token_url' => 'https://m1demo.test-madia.nl/oauth/token',
            'products' => 'https://m1demo.test-madia.nl/api/rest/products',
            'infos_url' => 'https://m1demo.test-madia.nl/',
            'identifier' => 'user.username',
            'nickname' => 'user.username',
            'realname' => 'user.display_name',
            'profilepicture' => 'user.avatar',
            'client_id' => '26e7ed48f6a321e359c1df944198d600',
            'client_secret' => 'ff3f08683d4fd5e39782703965eb0519',
        ));
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
     * Retrieve products
     */
    public function getProducts(array $accessToken, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ), $extraParameters);

        $url = $this->options['products'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_GET,
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->httpRequest($this->options['products'], null, $parameters, array('Content-Type' => 'application/json', 'Accept' => '*/*'))->getContent();

//        TODO FIX ME:
//        echo($content);
//        die(__METHOD__ . '####'. __LINE__);

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * Retrieve products
     */
    public function createProducts(array $accessToken, $content, $extraParameters = array())
    {
        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ), $extraParameters);

        $url = $this->options['products'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_POST,
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->httpRequest($url, $content, $parameters, array('Content-Type' => 'application/json'), HttpRequestInterface::METHOD_POST)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $token = array())
    {
//        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], array('oauth_token' => $token['oauth_token']));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {

//        var_dump($request->query->get('oauth_token'));
//        var_dump($request->query->get('oauth_verifier'));
//        die(__METHOD__ .'###'. __LINE__);


//        try {
//            if (null === $requestToken = $this->storage->fetch($this, $request->query->get('oauth_token'))) {
//                throw new \RuntimeException('No request token found in the storage.');
//            }
//        } catch (\InvalidArgumentException $e) {
//            print_r($e->__toString());die(__METHOD__ .'###'. __LINE__);
//            throw new AuthenticationException('Given token is not valid.');
//        }

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
            $request->query->get('oauth_token_secret'),
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

        return $response;
    }
}
