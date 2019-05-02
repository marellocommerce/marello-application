<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Variant;

class VariantController extends Controller
{
    /**
     * @Config\Route("/create/parent/{id}", requirements={"id"="\d+"}, name="marello_product_create_variant")
     * @Config\Method({"GET", "POST"})
     * @AclAncestor("marello_product_create_variant")
     * @Config\Template("MarelloProductBundle:Variant:update.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function createVariantAction(Product $product)
    {
        return $this->updateVariant($product, new Variant());
    }

    /**
     * @Config\Route(
     *     "/add/{id}/parent/{parentId}",
     *     requirements={"id"="\d+","parentId"="\d+"}, name="marello_product_add_variant"
     * )
     * @AclAncestor("marello_product_add_variant")
     * @Config\Template("MarelloProductBundle:Variant:update.html.twig")
     *
     * @param Request $request
     * @param Variant $variant
     * @return array|Response
     * @throws NotFoundHttpException
     */
    public function updateVariantAction(Request $request, Variant $variant)
    {
        $entityClass = $request->get('entityClass');

        if (!$entityClass) {
            throw new NotFoundHttpException(sprintf('Entity class "%s" is not found', $entityClass));
        }

        $entityClass = $this->get('oro_entity.routing_helper')->resolveEntityClass($entityClass);
        $parentId = $request->get('parentId');

        if ($parentId && $entityClass === $this->container->getParameter('marello_product.entity.class')) {
            $repository = $this->getDoctrine()->getRepository($entityClass);
            /** @var Product $parent */
            $parent = $repository->find($parentId);

            return $this->updateVariant($parent, $variant);
        }

        return new Response('', Response::HTTP_BAD_REQUEST);
    }

    /**
     * Process request and return the correct view to display
     * @param Product $product
     * @param Variant $variant
     * @return array
     */
    protected function updateVariant(Product $product, Variant $variant)
    {
        $handler = $this->get('marello_product.product_variant_form.handler');

        /*
         * Process request using handler.
         */
        if ($handler->process($variant, $product)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.product.messages.success.variant.saved')
            );

            /*
             * Redirect to product page.
             */
            return $this->get('oro_ui.router')->redirectAfterSave(
                [],
                [
                    'route'      => 'marello_product_view',
                    'parameters' => [
                        'id'                      => $product->getId(),
                        '_enableContentProviders' => 'mainMenu'
                    ]
                ],
                $product
            );
        }

        return [
            'entity' => $variant,
            'parent' => $product,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Config\Route("/widget/info/{id}", name="marello_product_variant_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("marello_product_view")
     * @Config\Template
     *
     * @param Product $product
     * @return array
     */
    public function infoAction(Product $product)
    {
        return [
            'product' => $product
        ];
    }
}
