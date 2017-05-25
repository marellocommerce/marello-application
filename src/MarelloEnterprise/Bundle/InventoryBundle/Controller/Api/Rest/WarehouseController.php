<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

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
