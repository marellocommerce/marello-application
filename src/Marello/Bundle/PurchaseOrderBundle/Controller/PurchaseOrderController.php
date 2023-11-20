<?php

namespace Marello\Bundle\PurchaseOrderBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateHandler;
use Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateStepOneHandler;
use Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderUpdateHandler;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepOneType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepTwoType;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchaseOrderController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_purchaseorder_purchaseorder_index"
     * )
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/index.html.twig")
     * @AclAncestor("marello_purchase_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => PurchaseOrder::class];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_purchaseorder_purchaseorder_view"
     * )
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/view.html.twig")
     * @AclAncestor("marello_purchase_order_view")
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return array
     */
    public function viewAction(PurchaseOrder $purchaseOrder)
    {
        return [
            'entity' => $purchaseOrder,
        ];
    }

    /**
     * @Route(
     *     path="/select-products",
     *     name="marello_purchaseorder_purchaseorder_selectproducts"
     * )
     * @Config\Template
     * @AclAncestor("marello_purchase_order_create")
     */
    public function selectProductsAction()
    {
        return [];
    }

    /**
     * @Route(
     *     path="/create",
     *     name="marello_purchaseorder_purchaseorder_create"
     * )
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/createStepOne.html.twig")
     * @AclAncestor("marello_purchase_order_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->createStepOne($request);
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_purchaseorder_purchaseorder_update"
     * )
     * @AclAncestor("marello_purchase_order_update")
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/update.html.twig")
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return array
     */
    public function updateAction(PurchaseOrder $purchaseOrder)
    {
        return $this->update($purchaseOrder);
    }

    /**
     * @Route(
     *     path="/create/step-two",
     *     name="marello_purchaseorder_purchaseorder_create_step_two"
     * )
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/createStepTwo.html.twig")
     * @AclAncestor("marello_purchase_order_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createStepTwoAction(Request $request)
    {
        return $this->createStepTwo($request, new PurchaseOrder());
    }

    /**
     * @param Request $request
     * @return array|Response
     */
    protected function createStepOne(Request $request)
    {
        $form = $this->createForm(PurchaseOrderCreateStepOneType::class, new PurchaseOrder());
        $handler = new PurchaseOrderCreateStepOneHandler($form, $request);
        $queryParams = $request->query->all();

        if ($handler->process()) {
            return $this->forward(__CLASS__ . '::createStepTwoAction', [], $queryParams);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @param PurchaseOrder $purchaseOrder
     * @return array|Response
     */
    protected function createStepTwo(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($request->request->get('input_action') === 'marello_purchaseorder_purchaseorder_create') {
            $form = $this->createForm(PurchaseOrderCreateStepOneType::class, $purchaseOrder);
            $queryParams = $request->query->all();
            $form->handleRequest($request);
            $formData = $form->all();

            if (!empty($formData)) {
                $form = $this->createForm(PurchaseOrderCreateStepTwoType::class, $purchaseOrder);
                foreach ($formData as $key => $item) {
                    $data = $item->getData();
                    $form->get($key)->setData($data);
                }
            }

            return [
                'form' => $form->createView(),
                'entity' => $purchaseOrder,
                'queryParams' => $queryParams
            ];
        }

        $handler = $this->container->get(PurchaseOrderCreateHandler::class);
        $form = $handler->getForm();

        if ($handler->handle()) {
            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.purchaseorder.messages.purchaseorder.saved')
            );
            return $this->container->get(Router::class)->redirect($purchaseOrder);
        }

        $this->addFlash(
            'error',
            $this->container->get(TranslatorInterface::class)->trans('marello.purchaseorder.messages.purchaseorder.not_saved')
        );

        if ($form->getErrors()->count() > 0) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
            'entity' => $purchaseOrder
        ];
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     *
     * @return array
     */
    protected function update(PurchaseOrder $purchaseOrder)
    {
        $handler = $this->container->get(PurchaseOrderUpdateHandler::class);

        if ($handler->process($purchaseOrder)) {
            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.purchaseorder.messages.purchaseorder.saved')
            );


            return $this->container->get(Router::class)->redirect($purchaseOrder);
        }

        return [
            'entity' => $purchaseOrder,
            'form'   => $handler->getForm()->createView(),
        ];
    }

    /**
     * @param PurchaseOrder|null $purchaseOrder
     * @Route(
     *      path="/widget/products/{id}",
     *      name="marello_purchase_order_widget_products_by_supplier",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("marello_product_view")
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/widget/productsBySupplier.html.twig")
     * @return array
     */
    public function productsBySupplierAction(PurchaseOrder $purchaseOrder = null)
    {
        $supplier = $this->container->get(ManagerRegistry::class)
            ->getManagerForClass(Supplier::class)
            ->getRepository(Supplier::class)
            ->find($this->container->get(RequestStack::class)->getCurrentRequest()->get('supplierId'));

        return [
            'purchaseOrder' => $purchaseOrder,
            'supplierId' => $supplier->getId(),
            'currency' => $this->container->get(CurrencyNameHelper::class)->getCurrencyName($supplier->getCurrency())
        ];
    }

    /**
     * @Route(
     *     path="/supplier-product-price/{productId}/{supplierId}",
     *     methods={"GET"},
     *     name="marello_purchase_order_supplier_product_price"
     * )
     * @Config\ParamConverter("product", options={"mapping": {"productId" : "id"}})
     * @Config\ParamConverter("supplier", options={"mapping": {"supplierId"   : "id"}})
     * @AclAncestor("marello_product_view")
     *
     * @param Product $product
     * @param Supplier $supplier
     * @return JsonResponse
     */
    public function getSupplierProductPriceAction(Product $product, Supplier $supplier)
    {
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            /** @var ProductSupplierRelation $productSupplierRelation */
            if ($productSupplierRelation->getSupplier()->getId() === $supplier->getId()) {
                return new JsonResponse(['purchasePrice' => round($productSupplierRelation->getCost(), 2)]);
            }
        }

        return new JsonResponse(['purchasePrice' => null]);
    }

    /**
     * @Route(
     *      path="/widget/purchase-order-candidates-grid",
     *      name="marello_purchase_order_widget_purchase_order_candidates_grid"
     * )
     * @AclAncestor("marello_product_view")
     * @Config\Template("@MarelloPurchaseOrder/PurchaseOrder/widget/purchaseOrderCandidatesGrid.html.twig")
     * @return array
     */
    public function purchaseOrderCandidatesGridAction()
    {
        return [];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                PurchaseOrderCreateHandler::class,
                TranslatorInterface::class,
                Router::class,
                PurchaseOrderUpdateHandler::class,
                ManagerRegistry::class,
                RequestStack::class,
                CurrencyNameHelper::class,
            ]
        );
    }
}
