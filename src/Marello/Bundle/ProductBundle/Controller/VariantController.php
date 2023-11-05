<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\ProductBundle\Form\Handler\ProductVariantHandler;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Variant;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @Template("@MarelloProduct/Variant/update.html.twig")
     *
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function createVariantAction(Product $product, Request $request)
    {
        return $this->updateVariant($product, new Variant(), $request);
    }

    /**
     * @Route(
     *     path="/add/{id}/parent/{parentId}",
     *     requirements={"id"="\d+","parentId"="\d+"}, name="marello_product_add_variant"
     * )
     * @AclAncestor("marello_product_add_variant")
     * @Template("@MarelloProduct/Variant/update.html.twig")
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

        $entityClass = $this->container->get(EntityRoutingHelper::class)->resolveEntityClass($entityClass);
        $parentId = $request->get('parentId');

        if ($parentId && $entityClass === Product::class) {
            $repository = $this->container->get(ManagerRegistry::class)->getRepository($entityClass);
            /** @var Product $parent */
            $parent = $repository->find($parentId);

            return $this->updateVariant($parent, $variant, $request);
        }

        return new Response('', Response::HTTP_BAD_REQUEST);
    }

    /**
     * Process request and return the correct view to display
     * @param Product $product
     * @param Variant $variant
     * @param Request $request
     * @return array
     */
    protected function updateVariant(Product $product, Variant $variant, Request $request)
    {
        $handler = $this->container->get(ProductVariantHandler::class);

        /*
         * Process request using handler.
         */
        if ($handler->process($variant, $product)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.product.messages.success.variant.saved')
            );

            /*
             * Redirect to product page.
             */
            return $this->container->get(Router::class)->redirect($product);
        }

        return [
            'entity' => $variant,
            'parent' => $product,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @deprecated rendering of widget is obsolete and done directly in product template.
     * See MarelloProduct/Product/view.html.twig#121
     * @Route(
     *     path="/widget/info/{id}",
     *     name="marello_product_variant_widget_info",
     *     requirements={"id"="\d+"}
     * )
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

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EntityRoutingHelper::class,
                ProductVariantHandler::class,
                TranslatorInterface::class,
                Router::class,
                ManagerRegistry::class,
            ]
        );
    }
}
