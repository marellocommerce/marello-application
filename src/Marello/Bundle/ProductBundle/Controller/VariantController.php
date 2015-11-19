<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Variant;

class VariantController extends Controller
{
    /**
     * @Route("/variant/create/parent/{id}", requirements={"id"="\d+"}, name="marello_product_create_variant")
     * @Acl(
     *      id="marello_product_create_variant",
     *      type="entity",
     *      permission="CREATE",
     *      class="MarelloProductBundle:Variant"
     * )
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
     * @Route("/variant/add/{id}/parent/{parentId}", requirements={"id"="\d+","parentId"="\d+"}, name="marello_product_add_variant")
     * @Acl(
     *      id="marello_product_add_variant",
     *      type="entity",
     *      permission="EDIT",
     *      class="MarelloProductBundle:Variant"
     * )
     * @Template("MarelloProductBundle:Variant:update.html.twig")
     *
     * @param Request $request
     * @param Variant $variant
     * @return array
     * @throws NotFoundHttpException
     */
    public function updateVariantAction(Request $request, Variant $variant)
    {
        $entityClass = $request->get('entityClass');

        if ($entityClass) {
            $entityClass = $this->get('oro_entity.routing_helper')->decodeClassName($entityClass);
            $parentId = $request->get('parentId');
            if ($parentId && $entityClass === $this->container->getParameter('marello_product.entity.class')) {
                $repository = $this->getDoctrine()->getRepository($entityClass);
                /** @var Product $parent */
                $parent = $repository->find($parentId);
                return $this->updateVariant($parent, $variant);
            }
        } else {
            throw new NotFoundHttpException(sprintf('Entity class "%s" is not found', $entityClass));
        }
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
        //add product to the handler
        if ($handler->process($variant, $product)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                    $this->get('translator')->trans('marello.product.messages.success.variant.saved')
            );

            //redirect to the product page
            return $this->get('oro_ui.router')->redirectAfterSave(
                array(),
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
     * @Route("/variant/widget/info/{id}", name="marello_product_variant_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("marello_product_view")
     * @Template()
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


