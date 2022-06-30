<?php

namespace Marello\Bundle\UPSBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\UPSBundle\Connection\Validator\UpsConnectionValidator;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\UPSBundle\Connection\Validator\Result\Factory\UpsConnectionValidatorResultFactory;
use Marello\Bundle\UPSBundle\Connection\Validator\Result\UpsConnectionValidatorResultInterface;
use Marello\Bundle\UPSBundle\Entity\Repository\ShippingServiceRepository;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxUPSController extends AbstractController
{
    /**
     * @Route(
     *     path="/get-shipping-services-by-country/{code}",
     *     methods={"GET"},
     *     name="marello_ups_country_shipping_services",
     *     requirements={"code"="^[A-Z]{2}$"}
     * )
     * @ParamConverter("country", options={"id" = "code"})
     * @param Country $country
     * @return JsonResponse
     */
    public function getShippingServicesByCountryAction(Country $country)
    {
        /** @var ShippingServiceRepository $repository */
        $repository = $this->container->get(ManagerRegistry::class)
            ->getManagerForClass('MarelloUPSBundle:ShippingService')
            ->getRepository('MarelloUPSBundle:ShippingService');
        $services = $repository->getShippingServicesByCountry($country);
        $result = [];
        foreach ($services as $service) {
            $result[] = ['id' => $service->getId(), 'description' => $service->getDescription()];
        }
        return new JsonResponse($result);
    }

    /**
     * @Route(
     *     path="/validate-connection/{channelId}/",
     *     methods={"POST"},
     *     name="marello_ups_validate_connection"
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

        /** @var UPSSettings $transport */
        $transport = $channel->getTransport();
        $result = $this->container->get(UpsConnectionValidator::class)->validateConnectionByUpsSettings($transport);

        if (!$result->getStatus()) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getErrorMessageByValidatorResult($result),
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => $this->container
                ->get(TranslatorInterface::class)
                ->trans('marello.ups.connection_validation.result.success.message'),
        ]);
    }

    /**
     * @param UpsConnectionValidatorResultInterface $result
     *
     * @return string
     */
    private function getErrorMessageByValidatorResult(UpsConnectionValidatorResultInterface $result)
    {
        $message = 'marello.ups.connection_validation.result.unexpected_error.message';
        $parameters = [
            '%error_message%' => trim($result->getErrorMessage(), '.')
        ];
        switch ($result->getErrorSeverity()) {
            case UpsConnectionValidatorResultFactory::AUTHENTICATION_SEVERITY:
                $message = 'marello.ups.connection_validation.result.authentication.message';
                break;
            case UpsConnectionValidatorResultFactory::MEASUREMENT_SYSTEM_SEVERITY:
                $message = 'marello.ups.connection_validation.result.measurement_system.message';
                break;
            case UpsConnectionValidatorResultFactory::SERVER_SEVERITY:
                $message = 'marello.ups.connection_validation.result.server_error.message';
                break;
        }
        return $this->container->get(TranslatorInterface::class)->trans($message, $parameters);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ManagerRegistry::class,
                UpsConnectionValidator::class,
                TranslatorInterface::class,
            ]
        );
    }
}
