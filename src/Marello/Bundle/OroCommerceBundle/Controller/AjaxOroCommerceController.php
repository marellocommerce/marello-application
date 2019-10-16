<?php

namespace Marello\Bundle\OroCommerceBundle\Controller;

use Doctrine\Common\Cache\Cache;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Generator\CacheKeyGenerator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxOroCommerceController extends Controller
{
    /**
     * @Route("/get-business-units/{channelId}/", name="marello_orocommerce_get_businessunits")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getBusinessUnitsAction(Request $request, Channel $channel = null)
    {
        return $this->getIntegrationData(
            CacheKeyGenerator::BUSINESS_UNIT,
            'getBusinessUnits',
            'name',
            $this->getTransport($request, $channel)
        );
    }

    /**
     * @Route("/get-product-units/{channelId}/", name="marello_orocommerce_get_productunits")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getProductUnitsAction(Request $request, Channel $channel = null)
    {
        return $this->getIntegrationData(
            CacheKeyGenerator::PRODUCT_UNIT,
            'getProductUnits',
            'id',
            $this->getTransport($request, $channel)
        );
    }

    /**
     * @Route("/get-customer-tax-codes/{channelId}/", name="marello_orocommerce_get_customertaxcodes")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getCustomerTaxCodesAction(Request $request, Channel $channel = null)
    {
        return $this->getIntegrationData(
            CacheKeyGenerator::CUSTOMER_TAX_CODE,
            'getCustomerTaxCodes',
            'code',
            $this->getTransport($request, $channel)
        );
    }

    /**
     * @Route("/get-price-lists/{channelId}/", name="marello_orocommerce_get_pricelists")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getPriceListsAction(Request $request, Channel $channel = null)
    {
        $transport = $this->getTransport($request, $channel);
        $cacheKey = sprintf('%s_%s', CacheKeyGenerator::PRICE_LIST, $transport->getCurrency());
        return $this->getIntegrationData(
            $cacheKey,
            'getPriceLists',
            'name',
            $transport
        );
    }

    /**
     * @Route("/get-product-families/{channelId}/", name="marello_orocommerce_get_productfamilies")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getProductFamiliesAction(Request $request, Channel $channel = null)
    {
        return $this->getIntegrationData(
            CacheKeyGenerator::PRODUCT_FAMILY,
            'getProductFamilies',
            'label',
            $this->getTransport($request, $channel)
        );
    }

    /**
     * @Route("/get-warehouses/{channelId}/", name="marello_orocommerce_get_warehouses")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Channel $channel
     * @return JsonResponse
     */
    public function getWarehousesAction(Request $request, Channel $channel = null)
    {
        return $this->getIntegrationData(
            CacheKeyGenerator::WAREHOUSE,
            'getWarehouses',
            'name',
            $this->getTransport($request, $channel)
        );
    }

    /**
     * @param Request $request
     * @param Channel|null $channel
     * @return OroCommerceSettings
     */
    private function getTransport(Request $request, Channel $channel = null)
    {
        if (!$channel) {
            $channel = new Channel();
        }

        $form = $this->createForm(
            ChannelType::class,
            $channel
        );
        $form->handleRequest($request);

        return $channel->getTransport();
    }

    /**
     * @param string $cacheKey
     * @param string $method
     * @param string $attribute
     * @param OroCommerceSettings $transport
     * @return JsonResponse
     */
    private function getIntegrationData($cacheKey, $method, $attribute, OroCommerceSettings $transport)
    {
        /** @var Cache $cache */
        $cache = $this->get('marello_orocommerce.cache');
        $keyGenerator = $this->get('marello_orocommerce.cache_key_generator');
        $key = sprintf(
            '%s_%s',
            $keyGenerator->generateKey($transport->getSettingsBag()),
            $cacheKey
        );

        if ($cache->contains($key)) {
            $jsonResult = $cache->fetch($key);
        } else {
            try {
                $result = $this
                    ->get('marello_orocommerce.integration.transport')
                    ->init($transport)
                    ->$method();
            } catch (RestException $e) {
                return new JsonResponse([]);
            }

            $jsonResult = [];

            foreach ($result['data'] as $item) {
                if ($attribute === 'id') {
                    $label = $item['id'];
                } else {
                    $label = $item['attributes'][$attribute];
                }
                $jsonResult[] = [
                    'value' => $item['id'],
                    'label' => $label
                ];
            }
            if (!empty($jsonResult)) {
                $cache->save(
                    $key,
                    $jsonResult
                );
            }
        }

        return new JsonResponse($jsonResult);
    }

    /**
     * @Route("/validate-connection/{channelId}/", name="marello_orocommerce_validate_connection")
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
     * @Method("POST")
     *
     * @param Request      $request
     * @param Channel|null $channel
     *
     * @return JsonResponse
     */
    public function validateConnectionAction(Request $request, Channel $channel = null)
    {
        if (!$channel) {
            $channel = new Channel();
        }

        $form = $this->createForm(
            ChannelType::class,
            $channel
        );
        $form->handleRequest($request);

        /** @var OroCommerceSettings $transport */
        $transport = $channel->getTransport();
        $result = $this
            ->get('marello_orocommerce.integration.transport')
            ->init($transport)
            ->ping();

        if ($result['result'] === false) {
            return new JsonResponse([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => $this
                ->get('translator')
                ->trans('marello.orocommerce.connection_validation.success.message'),
        ]);
    }
}
