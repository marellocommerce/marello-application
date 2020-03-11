<?php

namespace Marello\Bundle\OroCommerceBundle\Controller;

use Doctrine\Common\Cache\Cache;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Generator\CacheKeyGenerator;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;

class AjaxOroCommerceController extends AbstractController
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var CacheKeyGenerator
     */
    private $cacheKeyGenerator;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param Cache $cache
     * @param CacheKeyGenerator $cacheKeyGenerator
     * @param TransportInterface $transport
     * @param Translator $translator
     */
    public function __construct(
        Cache $cache,
        CacheKeyGenerator $cacheKeyGenerator,
        TransportInterface $transport,
        Translator $translator
    ) {
        $this->cache = $cache;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->transport = $transport;
        $this->translator = $translator;
    }

    /**
     * @Route(
     *     path="/get-business-units/{channelId}/",
     *     name="marello_orocommerce_get_businessunits",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
     * @Route(
     *     path="/get-product-units/{channelId}/",
     *     name="marello_orocommerce_get_productunits",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
     * @Route(
     *     path="/get-customer-tax-codes/{channelId}/",
     *     name="marello_orocommerce_get_customertaxcodes",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
     * @Route(
     *     path="/get-price-lists/{channelId}/",
     *     name="marello_orocommerce_get_pricelists",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
     * @Route(
     *     path="/get-product-families/{channelId}/",
     *     name="marello_orocommerce_get_productfamilies",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
     * @Route(
     *     path="/get-warehouses/{channelId}/",
     *     name="marello_orocommerce_get_warehouses",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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
        $key = sprintf(
            '%s_%s',
            $this->cacheKeyGenerator->generateKey($transport->getSettingsBag()),
            $cacheKey
        );

        if ($this->cache->contains($key)) {
            $jsonResult = $this->cache->fetch($key);
        } else {
            try {
                $result = $this->transport
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
                $this->cache->save(
                    $key,
                    $jsonResult
                );
            }
        }

        return new JsonResponse($jsonResult);
    }

    /**
     * @Route(
     *     path="/validate-connection/{channelId}/",
     *     name="marello_orocommerce_validate_connection",
     *     methods={"POST"}
     * )
     * @ParamConverter("channel", class="OroIntegrationBundle:Channel", options={"id" = "channelId"})
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

        /** @var OroCommerceSettings $transportSettings */
        $transportSettings = $channel->getTransport();
        $result = $this
            ->transport
            ->init($transportSettings)
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
                ->translator
                ->trans('marello.orocommerce.connection_validation.success.message'),
        ]);
    }
}
