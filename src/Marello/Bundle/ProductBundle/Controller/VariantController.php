<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Variant;

class VariantController extends AbstractController
{
    /**
     * @Route(
     *     path="/create/parent/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_product_create_variant"
     * )
     * @AclAncestor("marello_product_create_variant")
     * @Template("MarelloProductBundle:Variant:update.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function createVariantAction(Product $product)
    {
        return $this->updateVariant($product, new Variant());
    }

    /**
     * @Route(
     *     path="/add/{id}/parent/{parentId}",
     *     requirements={"id"="\d+","parentId"="\d+"}, name="marello_product_add_variant"
     * )
     * @AclAncestor("marello_product_add_variant")
     * @Template("MarelloProductBundle:Variant:update.html.twig")
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

        if ($parentId && $entityClass === Product::class) {
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
     * @Route(path="/widget/info/{id}", name="marello_product_variant_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("marello_product_view")
     * @Template
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
