<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelTypeType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesChannelTypeController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws HttpException|AccessDeniedHttpException
     *
     * @Route(
     *     path="/search",
     *     name="marello_sales_saleschanneltype_search"
     * )
     */
    public function searchAction(Request $request)
    {
        $query = $request->query->get('query');
        $result              = [
            'results' => [],
            'hasMore' => false,
            'errors'  => []
        ];
        $doctrine = $this->container->get(ManagerRegistry::class);
        if (empty($query)) {
            $channelTypes = $doctrine
                ->getManagerForClass(SalesChannelType::class)
                ->getRepository(SalesChannelType::class)
                ->findAll();
        } else {
            $channelTypes = $doctrine
                ->getManagerForClass(SalesChannelType::class)
                ->getRepository(SalesChannelType::class)
                ->search($query);
        }
        /** @var SalesChannelType[] $channelTypes */
        foreach ($channelTypes as $k => $channelType) {
            $result['results'][] = [
                'id' => $channelType->getName(),
                'label' => $channelType->getLabel(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_sales_saleschanneltype_create"
     * )
     * @Template("@MarelloSales/SalesChannelType/create.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $entity = new SalesChannelType();
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->createForm(SalesChannelTypeType::class, $entity),
            $this
                ->container
                ->get(TranslatorInterface::class)
                ->trans('marelloenterprise.inventory.messages.success.warehousechannelgrouplink.saved'),
            $request
        );
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ManagerRegistry::class,
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
            ]
        );
    }
}
