<?php

namespace Marello\Bundle\SupplierBundle\Controller;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Symfony\Component\Routing\Annotation\Route;

class SupplierController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_supplier_supplier_index"
     * )
     * @Template
     * @AclAncestor("marello_supplier_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloSupplierBundle:Supplier'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_supplier_supplier_view"
     * )
     * @Template
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
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_supplier_supplier_create"
     * )
     * @Template
     * @AclAncestor("marello_supplier_create")
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new Supplier());
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_supplier_supplier_update"
     * )
     * @Template
     * @AclAncestor("marello_supplier_update")
     *
     * @param Supplier $supplier
     *
     * @return array
     */
    public function updateAction(Supplier $supplier)
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
     * @Route(
     *     path="/widget/address/{id}/{typeId}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+","typeId"="\d+"},
     *     name="marello_supplier_supplier_address"
     * )
     * @Template("MarelloSupplierBundle:Supplier/widget:address.html.twig")
     * @AclAncestor("marello_supplier_update")
     *
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function addressAction(MarelloAddress $address)
    {
        return [
            'supplierAddress' => $address
        ];
    }

    /**
     * @Route(
     *     path="/update/address/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_supplier_supplier_updateaddress"
     * )
     * @Template("MarelloSupplierBundle:Supplier:widget/updateAddress.html.twig")
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
        $form  = $this->createForm(AddressType::class, $address);
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
     * @Route(
     *     path="/get-supplier-default-data",
     *     methods={"GET"},
     *     name="marello_supplier_supplier_get_default_data"
     * )
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
