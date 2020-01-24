<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Handler\ProductCreateStepOneHandler;
use Marello\Bundle\ProductBundle\Form\Type\ProductStepOneType;
use Marello\Bundle\ProductBundle\Form\Type\ProductType;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    const ACTION_SAVE_AND_DUPLICATE = 'save_and_duplicate';

    /**
     * @Config\Route("/", name="marello_product_index")
     * @AclAncestor("marello_product_view")
     * @Config\Template
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloProductBundle:Product'];
    }

    /**
     * @Config\Route("/create", name="marello_product_create")
     * @AclAncestor("marello_product_create")
     * @Config\Template("MarelloProductBundle:Product:createStepOne.html.twig")
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
     * @param Request $request
     * @return array|Response
     */
    protected function createStepOne(Request $request)
    {
        $form = $this->createForm(ProductStepOneType::class);
        $handler = new ProductCreateStepOneHandler($form, $request);
        $productTypesProvider = $this->get('marello_product.provider.product_types');

        if ($handler->process()) {
            return $this->forward('MarelloProductBundle:Product:createStepTwo');
        }
        if (count($productTypesProvider->getProductTypes()) === 1) {
            $request->setMethod('POST');
            $request->request->set('input_action', 'marello_product_create');
            $request->request->set('single_product_type', true);
            return $this->forward('MarelloProductBundle:Product:createStepTwo');
        }

        return [
            'form' => $form->createView(),
            'isWidgetContext' => (bool)$request->get('_wid', false)
        ];
    }

    /**
     * @Config\Route("/create/step-two", name="marello_product_create_step_two")
     *
     * @Config\Template("MarelloProductBundle:Product:createStepTwo.html.twig")
     *
     * @AclAncestor("marello_product_create")
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function createStepTwoAction(Request $request)
    {
        return $this->createStepTwo($request, new Product());
    }
    
    /**
     * @param Request $request
     * @param Product $product
     * @return array|RedirectResponse
     */
    protected function createStepTwo(Request $request, Product $product)
    {
        if ($request->get('input_action') === 'marello_product_create') {
            $formStepOne = $this->createForm(ProductStepOneType::class, $product);
            if ($request->get('single_product_type')) {
                $type = Product::DEFAULT_PRODUCT_TYPE;
            } else {
                $formStepOne->handleRequest($request);
                $type = $formStepOne->get('type')->getData();
            }
            $product->setType($type);
            $productTypesProvider = $this->get('marello_product.provider.product_types');
            $em = $this->get('doctrine.orm.entity_manager');
            $productType = $productTypesProvider->getProductType($type);
            if ($productType) {
                /** @var AttributeFamily $attributeFamily */
                $attributeFamily = $em
                    ->getRepository(AttributeFamily::class)
                    ->findOneBy(['code' => $productType->getAttributeFamilyCode()]);
                $product->setAttributeFamily($attributeFamily);

                $form = $this->createForm(ProductType::class, $product);
                $form->get('type')->setData($type);
                $form->get('attributeFamily')->setData($attributeFamily->getId());
            }
            return [
                'form' => $form->createView(),
                'entity' => $product,
                'isWidgetContext' => (bool)$request->get('_wid', false)
            ];
        }
        //$form = $this->createForm(ProductStepOneType::class, $product, ['validation_groups'=> false]);
        //$form->submit($request->request->get(ProductType::BLOCK_PREFIX));

        return $this->update($product, $request);
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

    /**
     * @Config\Route("/assign-sales-channels", name="marello_product_assign_sales_channels")
     * @AclAncestor("marello_product_update")
     * @Config\Template
     *
     * @return array
     */
    public function assignSalesChannelsAction()
    {
        $handler = $this->get('marello_product.sales_channels_assign.handler');
        $result = $handler->process();

        if (true === $result['success']) {
            $this->get('session')->getFlashBag()->add(
                $result['type'],
                $this->get('translator')->trans($result['message'])
            );

            return $this->redirectToRoute('marello_product_index');
        }

        return [
            'form' => $handler->getFormView(),
        ];
    }
}
