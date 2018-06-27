<?php

namespace Marello\Bundle\SupplierBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class SupplierController extends Controller
{
    /**
     * @Config\Route("/", name="marello_supplier_supplier_index")
     * @Config\Template
     * @AclAncestor("marello_supplier_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloSupplierBundle:Supplier'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_supplier_supplier_view")
     * @Config\Template
     * @AclAncestor("marello_supplier_view")
     *
     * @param Supplier $supplier
     *
     * @return array
     */
    public function viewAction(Supplier $supplier)
    {
        return ['entity' => $supplier];
    }

    /**
     * @Config\Route("/create", name="marello_supplier_supplier_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_supplier_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new Supplier());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_supplier_supplier_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_supplier_update")
     *
     * @param Request $request
     * @param Supplier   $supplier
     *
     * @return array
     */
    public function updateAction(Request $request, Supplier $supplier)
    {
        return $this->update($supplier);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param Supplier   $supplier
     *
     * @return array
     */
    protected function update(Supplier $supplier = null)
    {
        $handler = $this->get('marello_supplier.form.handler.supplier');
        
        if ($handler->process($supplier)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.supplier.messages.success.supplier.saved')
            );
            
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_supplier_supplier_update',
                    'parameters' => [
                        'id' => $supplier->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_supplier_supplier_view',
                    'parameters' => [
                        'id' => $supplier->getId(),
                    ],
                ],
                $supplier
            );
        }

        return [
            'entity' => $supplier,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Config\Route(
     *     "/widget/address/{id}/{typeId}",
     *     requirements={"id"="\d+","typeId"="\d+"},
     *     name="marello_supplier_supplier_address"
     * )
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_supplier_update")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function addressAction(Request $request, MarelloAddress $address)
    {
        return [
            'supplierAddress' => $address
        ];
    }

    /**
     * @Config\Route("/update/address/{id}", requirements={"id"="\d+"}, name="marello_supplier_supplier_updateaddress")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSupplierBundle:Supplier:widget/updateAddress.html.twig")
     * @AclAncestor("marello_supplier_update")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function updateAddressAction(Request $request, MarelloAddress $address)
    {
        $responseData = array(
            'saved' => false,
        );
        $form  = $this->createForm('marello_address', $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['supplierAddress'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        return $responseData;
    }

    /**
     * @Config\Route("/get-supplier-default-data", name="marello_supplier_supplier_get_default_data")
     * @Config\Method({"GET"})
     * @AclAncestor("marello_supplier_view")
     *
     * {@inheritdoc}
     */
    public function getSupplierDefaultDataAction(Request $request)
    {
        return new JsonResponse(
            $this->get('marello_supplier.provider.supplier')->getSupplierDefaultDataById(
                $request->query->get('supplier_id')
            )
        );
    }
}
