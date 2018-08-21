<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ProductController extends Controller
{
    const ACTION_SAVE_AND_DUPLICATE = 'save_and_duplicate';

    /**
     * @Config\Route(
     *      "/{_format}",
     *      name="marello_product_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @AclAncestor("marello_product_view")
     * @Config\Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Config\Route("/create", name="marello_product_create")
     * @AclAncestor("marello_product_create")
     * @Config\Template("MarelloProductBundle:Product:update.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new Product(), $request);
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_product_update")
     * @AclAncestor("marello_product_update")
     * @Config\Template
     *
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    public function updateAction(Product $product, Request $request)
    {
        return $this->update($product, $request);
    }

    /**
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    protected function update(Product $product, Request $request)
    {
        $handler = $this->get('marello_product.product_form.handler');

        if ($handler->process($product)) {
            if ($request->get(Router::ACTION_PARAMETER) === self::ACTION_SAVE_AND_DUPLICATE) {
                $saveMessage = $this->get('translator')
                    ->trans('marello.product.ui.product.saved_and_duplicated.message');
                $this->get('session')->getFlashBag()->set('success', $saveMessage);
                $actionGroup = $this->get('oro_action.action_group_registry')->findByName('marello_product_duplicate');
                if ($actionGroup) {
                    $actionData = $actionGroup->execute(new ActionData(['data' => $product]));
                    /** @var Product $productCopy */
                    if ($productCopy = $actionData->offsetGet('productCopy')) {
                        return new RedirectResponse(
                            $this->get('router')->generate('marello_product_view', ['id' => $productCopy->getId()])
                        );
                    }
                }
            } else {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('marello.product.messages.success.product.saved')
                );

                return $this->get('oro_ui.router')->redirectAfterSave(
                    [
                        'route' => 'marello_product_update',
                        'parameters' => [
                            'id' => $product->getId(),
                        ]
                    ],
                    [
                        'route' => 'marello_product_view',
                        'parameters' => [
                            'id' => $product->getId(),
                        ]
                    ],
                    $product
                );
            }
        }

        return [
            'entity' => $product,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_product_view")
     * @AclAncestor("marello_product_view")
     * @Config\Template("MarelloProductBundle:Product:view.html.twig")
     *
     * @param Product $product
     *
     * @return array
     */
    public function viewAction(Product $product)
    {
        return [
            'entity' => $product,
        ];
    }

    /**
     * @Config\Route("/widget/info/{id}", name="marello_product_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("marello_product_view")
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function infoAction(Product $product)
    {
        return [
            'product' => $product
        ];
    }

    /**
     * @Config\Route("/widget/price/{id}", name="marello_product_widget_price", requirements={"id"="\d+"})
     * @AclAncestor("marello_product_view")
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function priceAction(Product $product)
    {
        return [
            'product' => $product
        ];
    }
}
