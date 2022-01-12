<?php

namespace Marello\Bundle\OroCommerceBundle\Request\Factory;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Request\OroCommerceRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class OroCommerceRequestFactory implements OroCommerceRequestFactoryInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    const EQ = 'eq';
    const NEQ = 'neq';
    const LT = 'lt';
    const LTE = 'lte';
    const GT = 'gt';
    const GTE = 'gte';
    
    /**
     * {@inheritdoc}
     */
    public static function createRequest(
        $method,
        ParameterBag $settingsBag,
        $resource,
        array $filters = [],
        array $include = [],
        array $data = []
    ) {
        $path = sprintf(
            '%s/%s',
            self::API_PATH,
            $resource
        );
        if (in_array($method, [self::METHOD_GET, self::METHOD_PATCH, self::METHOD_DELETE]) &&
            isset($data['data']['id'])) {
            $path = sprintf('%s/%s', $path, $data['data']['id']);
        }
        if (in_array($method, [self::METHOD_GET]) && count($include) > 0) {
            $path .= sprintf('?include=%s', implode(',', $include));
        }
        if (in_array($method, [self::METHOD_GET, self::METHOD_DELETE])) {
            if (count($filters) > 0) {
                if (count($include) === 0) {
                    $path = sprintf('%s?', $path);
                }
                foreach ($filters as $key => $filter) {
                    $value = $filter->getValue();
                    $path .= sprintf(
                        '%sfilter[%s][%s]=%s',
                        $key === 0 && count($include) === 0 ? '' : '&',
                        $filter->getPath(),
                        $filter->getOperator(),
                        is_array($value) ? implode(',', $value) : $value
                    );
                }
            }
        }
        $payload = [];
        if (in_array($method, [self::METHOD_POST, self::METHOD_PATCH])) {
            $payload = json_encode($data);
        }

        return new OroCommerceRequest([
            OroCommerceRequest::PATH_FIELD => $path,
            OroCommerceRequest::HEADERS_FIELD => self::getHeaders($settingsBag),
            OroCommerceRequest::PAYLOAD_FIELD => $payload
        ]);
    }

    /**
     * @param ParameterBag $settingsBag
     * @return array
     */
    protected static function getHeaders(ParameterBag $settingsBag)
    {
        $nonce = base64_encode(substr(md5(uniqid()), 0, 16));
        $created  = date('c');
        $digest   = base64_encode(
            sha1(base64_decode($nonce) . $created . $settingsBag->get(OroCommerceSettings::KEY_FIELD), true)
        );

        return [
            'Authorization' => 'WSSE profile="UsernameToken"',
            'X-WSSE' => sprintf(
                'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $settingsBag->get(OroCommerceSettings::USERNAME_FIELD),
                $digest,
                $nonce,
                $created
            ),
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json'
        ];
    }
}
