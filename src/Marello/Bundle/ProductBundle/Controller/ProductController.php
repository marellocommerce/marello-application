<?php

namespace Marello\Bundle\ProductBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Handler\ProductCreateStepOneHandler;
use Marello\Bundle\ProductBundle\Form\Handler\ProductHandler;
use Marello\Bundle\ProductBundle\Form\Handler\ProductsSalesChannelsAssignHandler;
use Marello\Bundle\ProductBundle\Form\Type\ProductStepOneType;
use Marello\Bundle\ProductBundle\Form\Type\ProductType;
use Marello\Bundle\ProductBundle\Provider\ActionGroupRegistryProvider;
use Marello\Bundle\ProductBundle\Provider\ProductTypesProvider;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductController extends AbstractController
{
    const ACTION_SAVE_AND_DUPLICATE = 'save_and_duplicate';

    /**
     * @Route(
     *     path="/",
     *     name="marello_product_index"
     * )
     * @AclAncestor("marello_product_view")
     * @Template
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloProductBundle:Product'];
    }

    /**
     * @Route(
     *     path="/create",
     *     name="marello_product_create"
     * )
     * @AclAncestor("marello_product_create")
     * @Template("@MarelloProduct/Product/createStepOne.html.twig")
     *
     * @param Request $request
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
        $form = $this->createForm(ProductStepOneType::class, new Product());
        $handler = new ProductCreateStepOneHandler($form, $request);
        $queryParams = $request->query->all();

        if ($handler->process()) {
            return $this->forward(__CLASS__ . '::createStepTwoAction', [], $queryParams);
        }

        $productTypesProvider = $this->container->get(ProductTypesProvider::class);
        $em = $this->container->get(DoctrineHelper::class);
        /** @var AttributeFamily $attributeFamily */
        $countAttributeFamilies = $em
            ->getEntityRepositoryForClass(AttributeFamily::class)
            ->countFamiliesByEntityClass(Product::class);
        if (count($productTypesProvider->getProductTypes()) <= 1 && $countAttributeFamilies <= 1) {
            $request->setMethod('POST');
            $request->request->set('input_action', 'marello_product_create');
            $request->request->set('single_product_type', true);

            return $this->forward(__CLASS__ . '::createStepTwoAction', [], $queryParams);
        }

        return [
            'form' => $form->createView(),
            'isWidgetContext' => (bool)$request->get('_wid', false)
        ];
    }

    /**
     * @Route(
     *     path="/create/step-two",
     *     name="marello_product_create_step_two"
     * )
     *
     * @Template("@MarelloProduct/Product/createStepTwo.html.twig")
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
        if ($request->get(Router::ACTION_PARAMETER) === 'marello_product_create') {
            $form = $this->createForm(ProductStepOneType::class, $product);
            $queryParams = $request->query->all();
            $form->handleRequest($request);
            $formData = $form->all();

            if ($request->get('single_product_type')) {
                $em = $this->container->get(DoctrineHelper::class);
                /** @var AttributeFamily $attributeFamily */
                $attributeFamily = $em
                    ->getEntityRepositoryForClass(AttributeFamily::class)
                    ->findOneBy(['entityClass' => Product::class]);
                $product->setType(Product::DEFAULT_PRODUCT_TYPE);
                $product->setAttributeFamily($attributeFamily);
            }

            if (!empty($formData)) {
                $form = $this->createForm(ProductType::class, $product);
                foreach ($formData as $key => $item) {
                    $data = $item->getData();
                    $form->get($key)->setData($data);
                }
            }

            return [
                'form' => $form->createView(),
                'entity' => $product,
                'isWidgetContext' => (bool)$request->get('_wid', false),
                'queryParams' => $queryParams
            ];
        }

        return $this->update($product, $request);
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_product_update"
     * )
     * @AclAncestor("marello_product_update")
     * @Template
     *
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function updateAction(Product $product, Request $request)
    {
        return $this->update($product, $request);
    }

    /**
     * @param Product $product
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(Product $product, Request $request)
    {
        $handler = $this->container->get(ProductHandler::class);

        if ($handler->process($product)) {
            if ($request->get(Router::ACTION_PARAMETER) === self::ACTION_SAVE_AND_DUPLICATE) {
                $saveMessage = $this->container->get(TranslatorInterface::class)
                    ->trans('marello.product.ui.product.saved_and_duplicated.message');
                $request->getSession()->getFlashBag()->set('success', $saveMessage);
                $actionGroup = $this
                    ->container
                    ->get(ActionGroupRegistryProvider::class)
                    ->findActionByName('marello_product_duplicate');
                if ($actionGroup) {
                    $actionData = $actionGroup->execute(new ActionData(['data' => $product]));
                    /** @var Product $productCopy */
                    if ($productCopy = $actionData->offsetGet('productCopy')) {
                        return $this->redirectToRoute('marello_product_view', ['id' => $productCopy->getId()]);
                    }
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('marello.product.messages.success.product.saved')
                );

                return $this->container->get(Router::class)->redirect($product);
            }
        }

        return [
            'entity' => $product,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_product_view"
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloProduct/Product/view.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function viewAction(Product $product)
    {
        return [
            'entity' => $product,
        ];
    }

    /**
     * @Route(
     *     path="/widget/info/{id}",
     *     name="marello_product_widget_info",
     *     requirements={"id"="\d+"}
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloProduct/Product/widget/info.html.twig")
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

    /**
     * @Route(
     *     path="/widget/image/{id}",
     *     name="marello_product_widget_image",
     *     requirements={"id"="\d+"}
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloProduct/Product/widget/image.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function imageAction(Product $product)
    {
        return [
            'entity' => $product
        ];
    }

    /**
     * @Route(
     *     path="/widget/price/{id}",
     *     name="marello_product_widget_price",
     *     requirements={"id"="\d+"}
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloProduct/Product/widget/price.html.twig")
     *
     * @param Product $product
     * @return array
     */
    public function priceAction(Product $product)
    {
        return [
            'product' => $product
        ];
    }

    /**
     * @Route(
     *     path="/assign-sales-channels",
     *     name="marello_product_assign_sales_channels"
     * )
     * @AclAncestor("marello_product_update")
     * @Template
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function assignSalesChannelsAction(Request $request)
    {
        $handler = $this->container->get(ProductsSalesChannelsAssignHandler::class);
        $result = $handler->process();

        if (true === $result['success']) {
            $request->getSession()->getFlashBag()->add(
                $result['type'],
                $this->container->get(TranslatorInterface::class)->trans($result['message'])
            );

            return $this->redirectToRoute('marello_product_index');
        }

        return [
            'form' => $handler->getFormView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ProductTypesProvider::class,
                DoctrineHelper::class,
                ProductHandler::class,
                TranslatorInterface::class,
                ActionGroupRegistryProvider::class,
                ProductsSalesChannelsAssignHandler::class,
                Router::class,
            ]
        );
    }
}
