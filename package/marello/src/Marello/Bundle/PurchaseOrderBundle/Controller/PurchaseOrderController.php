<?php

namespace Marello\Bundle\PurchaseOrderBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateStepOneHandler;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepOneType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepTwoType;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class PurchaseOrderController extends Controller
{
    /**
     * @Config\Route("/", name="marello_purchaseorder_purchaseorder_index")
     * @Config\Template
     * @AclAncestor("marello_purchase_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => PurchaseOrder::class];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_purchaseorder_purchaseorder_view")
     * @Config\Template
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
     * @Config\Route("/select-products", name="marello_purchaseorder_purchaseorder_selectproducts")
     * @Config\Template
     * @AclAncestor("marello_purchase_order_create")
     */
    public function selectProductsAction()
    {
        return [];
    }

    /**
     * @Config\Route("/create", name="marello_purchaseorder_purchaseorder_create")
     * @Config\Template("MarelloPurchaseOrderBundle:PurchaseOrder:createStepOne.html.twig")
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_purchaseorder_purchaseorder_update")
     * @AclAncestor("marello_purchase_order_update")
     * @Config\Template
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
     * @Config\Route("/create/step-two", name="marello_purchaseorder_purchaseorder_create_step_two")
     * @Config\Template("MarelloPurchaseOrderBundle:PurchaseOrder:createStepTwo.html.twig")
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
        $form = $this->createForm(PurchaseOrderCreateStepOneType::class);
        $handler = new PurchaseOrderCreateStepOneHandler($form, $request);

        if ($handler->process()) {
            return $this->forward('MarelloPurchaseOrderBundle:PurchaseOrder:createStepTwo');
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
        if ($request->get('input_action') === 'marello_purchaseorder_purchaseorder_create') {
            $form = $this->createForm(PurchaseOrderCreateStepOneType::NAME, $purchaseOrder);
            $form->handleRequest($request);
            $formData = $form->all();

            if (!empty($formData)) {
                $form = $this->createForm(PurchaseOrderCreateStepTwoType::NAME, $purchaseOrder);
                foreach ($formData as $key => $item) {
                    $data = $item->getData();
                    $form->get($key)->setData($data);
                }
            }

            return [
                'form' => $form->createView(),
                'entity' => $purchaseOrder
            ];
        }

        $handler = $this->get("marello_purchase_order.form.handler.purchase_order_create");
        $form = $handler->getForm();

        if ($handler->handle()) {
            $this->addFlash(
                'success',
                $this->get('translator')->trans('marello.purchaseorder.messages.purchaseorder.saved')
            );
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_view',
                    'parameters' => [
                        'id' => $form->getData()->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_index',
                    'parameters' => [],
                ]
            );
        }

        $this->addFlash(
            'error',
            $this->get('translator')->trans('marello.purchaseorder.messages.purchaseorder.not_saved')
        );

        if (($e = $form->getErrorsAsString()) != '') {
            $this->addFlash('error', $e);
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
        $handler = $this->get('marello_purchase_order.form.handler.purchase_order_update');

        if ($handler->process($purchaseOrder)) {
            $this->addFlash(
                'success',
                $this->get('translator')->trans('marello.purchaseorder.messages.purchaseorder.saved')
            );


            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_update',
                    'parameters' => [
                        'id'                      => $purchaseOrder->getId(),
                    ]
                ],
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_view',
                    'parameters' => [
                        'id'                      => $purchaseOrder->getId(),
                    ]
                ],
                $purchaseOrder
            );
        }

        return [
            'entity' => $purchaseOrder,
            'form'   => $handler->getForm()->createView(),
        ];
    }

    /**
     * @Config\Route(
     *      "/widget/products/{id}",
     *      name="marello_purchase_order_widget_products_by_supplier",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("marello_product_view")
     * @Config\Template()
     */
    public function productsBySupplierAction(PurchaseOrder $purchaseOrder = null)
    {
        $supplier = $this->get('doctrine')
            ->getManagerForClass(Supplier::class)
            ->getRepository(Supplier::class)
            ->find($this->get('request')->get('supplierId'));

        return [
            'purchaseOrder' => $purchaseOrder,
            'supplierId' => $supplier->getId(),
            'currency' => $this->get('oro_currency.helper.currency_name')->getCurrencyName($supplier->getCurrency())
            
        ];
    }

    /**
     * @Config\Route("/supplier-product-price/{productId}/{supplierId}", name="marello_purchase_order_supplier_product_price")
     * @Config\ParamConverter("product", options={"mapping": {"productId" : "id"}})
     * @Config\ParamConverter("supplier", options={"mapping": {"supplierId"   : "id"}})
     * @Config\Method({"GET"})
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
}
