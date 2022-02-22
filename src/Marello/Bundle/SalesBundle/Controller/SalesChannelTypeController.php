<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

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
        $doctrine = $this->get('doctrine');
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
        return $this->get('oro_form.update_handler')->update(
            $entity,
            $this->createForm(SalesChannelTypeType::class, $entity),
            $this
                ->get('translator')
                ->trans('marelloenterprise.inventory.messages.success.warehousechannelgrouplink.saved'),
            $request
        );
    }
}
