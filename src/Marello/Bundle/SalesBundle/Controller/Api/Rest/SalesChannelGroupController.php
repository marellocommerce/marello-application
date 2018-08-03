<?php

namespace Marello\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("saleschannelgroup")
 * @Rest\NamePrefix("marello_sales_api_")
 */
class SalesChannelGroupController extends RestController implements ClassResourceInterface
{
    /**
     * Delete entity SalesChannelGroup
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete SalesChannelGroup from application"
     * )
     * @AclAncestor("marello_sales_saleschannelgroup_delete")
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
        return $this->get('marello_sales.saleschannelgroup.manager.api');
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
        return $this->get('marello_sales.handler.saleschannelgroup_delete');
    }
}
