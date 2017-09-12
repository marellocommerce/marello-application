<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use FOS\RestBundle\Util\Codes;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Annotation as Security;

/**
 * @Rest\RouteResource("warehouse")
 * @Rest\NamePrefix("marelloenterprise_inventory_api_")
 */
class WarehouseController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET warehouse address
     *
     * @ApiDoc(
     *      description="Get warehouse address"
     * )
     * @Security\AclAncestor("marello_inventory_warehouse_view")
     * @param Warehouse $warehouse
     * @return Response
     */
    public function getAddressAction(Warehouse $warehouse)
    {
        $address = null;
        if ($warehouse) {
            $addressEntity = $warehouse->getAddress();
            if ($addressEntity) {
                $address = $this->getPreparedItem($addressEntity);
                $address['countryIso2'] = $addressEntity->getCountryIso2();
                $address['countryIso3'] = $addressEntity->getCountryIso3();
                $address['regionCode'] = $addressEntity->getRegionCode();
                $address['country'] = $addressEntity->getCountryName();
            }
        }
        $responseData = $address ? json_encode($address) : '';

        return new Response($responseData, Codes::HTTP_OK);
    }
    
    /**
     * Delete entity Warehouse
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete warehouse from application"
     * )
     * @Security\AclAncestor("marelloenterprise_inventory_warehouse_delete")
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            $this->getDeleteHandler()->handleDelete($id, $this->getManager());

            return new JsonResponse(["id" => ""]);
        } catch (\Exception $e) {
            return new JsonResponse(["code" => $e->getCode(), "message" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('marelloenterprise.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteHandler()
    {
        return $this->get('marelloenterprise_inventory.form.type.warehouse_delete.handler');
    }
}
