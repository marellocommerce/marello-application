<?php

namespace Marello\Bundle\ReturnBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use Marello\Bundle\ReturnBundle\Form\Handler\ReturnApiHandler;

/**
 * @Rest\RouteResource("return")
 * @Rest\NamePrefix("marello_return_api_")
 */
class ReturnController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Page number, starting from 1. Defaults to 1."
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *     description="Get a list of all Return Entities",
     *     resource=true
     * )
     * @AclAncestor("marello_return_view")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get one Return entity by id",
     *     resource=true
     * )
     * @AclAncestor("marello_return_view")
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new Return
     *
     * @ApiDoc(
     *     description="Create a new Return via the Api",
     *     resource=true
     * )
     * @AclAncestor("marello_return_create")
     *
     * @return Response
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('marello_return.manager.return.api');
    }

    /**
     * @return ReturnApiHandler
     */
    public function getFormHandler()
    {
        return $this->get('marello_return.form.handler.return_api');
    }
}
