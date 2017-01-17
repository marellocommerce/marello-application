<?php

namespace Marello\Bundle\SupplierBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Symfony\Component\HttpFoundation\Response;

class SupplierController extends Controller
{
    /**
     * @Config\Route("/", name="marello_supplier_supplier_index")
     * @Config\Template
     * @Security\AclAncestor("marello_supplier_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloSupplierBundle:Supplier'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_supplier_view")
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
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_supplier_create")
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_supplier_update")
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
     * @Config\Route("/delete/{id}", requirements={"id":"\d+"})
     * @Config\Method("DELETE")
     * @Security\AclAncestor("marello_supplier_delete")
     *
     * @param Supplier $supplier
     *
     * @return RedirectResponse
     */
    public function deleteAction(Supplier $supplier)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($supplier);

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            /*
             * In case a foreign constraint would be violated when supplier is removed,
             * keep it and display message.
             *
             * Foreign constraint violation in this case means that there are still entities in marello,
             * which are associated to this particular supplier. These should be deleted before supplier itself.
             *
             * TODO: Display this message. When delete action returns code 500, it is overridden in js with a different
             *       one. Code 500 is the correct one that should be returned, so probably a modification in js will be
             *       needed.
             */
            $this->addFlash('error', 'marello.supplier.messages.supplier_has_associations');

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
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
     * @Config\Route("/widget/address/{id}/{typeId}", requirements={"id"="\d+","typeId"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_supplier_update")
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
     * @Config\Route("/update/address/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSupplierBundle:Supplier:widget/updateAddress.html.twig")
     * @Security\AclAncestor("marello_supplier_update")
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
     * @Config\Route("/get-suppliers-by-product", name="marello_supplier_supplier_data")
     * @Config\Method({"GET"})
     * @Security\AclAncestor("marello_product_view")
     *
     * {@inheritdoc}
     */
    public function getSupplierDataAction(Request $request)
    {
        //todo
        return new JsonResponse();
//        return new JsonResponse(
//            $this->get('marello_productprice.pricing.provider.channelprice_provider')->getPrices(
//                $request->query->get('salesChannel'),
//                $request->query->get('product_ids', [])
//            )
//        );
    }
}
