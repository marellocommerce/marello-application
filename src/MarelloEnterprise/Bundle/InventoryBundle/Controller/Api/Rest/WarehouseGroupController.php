<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("warehousegroup")
 * @Rest\NamePrefix("marelloenterprise_inventory_api_")
 */
class WarehouseGroupController extends RestController implements ClassResourceInterface
{
    /**
     * Delete entity WarehouseGroup
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete warehousegroup from application"
     * )
     * @AclAncestor("marelloenterprise_inventory_warehousegroup_delete")
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('marelloenterprise_inventory.warehousegroup.manager.api');
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
        return $this->get('marelloenterprise_inventory.handler.warehousegroup_delete');
    }
}
