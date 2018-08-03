<?php

namespace Marello\Bundle\OrderBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

/**
 * @Rest\RouteResource("customer")
 * @Rest\NamePrefix("marello_customer_api_")
 */
class CustomerController extends RestController implements ClassResourceInterface
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
     *     description="Get a list of all Customer Entities",
     *     resource=true
     * )
     * @AclAncestor("marello_customer_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET Customer by email
     *
     * @Rest\QueryParam(
     *      name="email",
     *      requirements="[a-zA-Z0-9\-_\.@]+",
     *      nullable=false,
     *      description="Email to filter"
     * )
     *
     * @ApiDoc(
     *      description="Get user by email",
     *      resource=true,
     *      filters={
     *          {"name"="email", "dataType"="string"}
     *      }
     * )
     * @Rest\Get("/customers/getcustomerbyemail")
     * @AclAncestor("marello_customer_view")
     * @param Request $request
     *
     * @return Response
     */
    public function getByEmailAction(Request $request)
    {
        $params = array_intersect_key(
            $request->query->all(),
            array_flip($this->getSupportedQueryParameters(__FUNCTION__))
        );

        if (empty($params)) {
            return $this->handleView(
                $this->view(
                    [
                        'message' => 'Customer not found'
                    ],
                    Codes::HTTP_NOT_FOUND
                )
            );
        }


        $entity = $this->getDoctrine()
            ->getRepository('MarelloOrderBundle:Customer')
            ->findOneBy($params);

        if (!$entity) {
            return $this->handleView(
                $this->view(
                    [
                        'message' => sprintf(
                            'Customer with email %s not found',
                            $params['email']
                        )
                    ],
                    Codes::HTTP_NOT_FOUND
                )
            );
        }

        $data['customer'] = $entity;


        return $this->handleView(
            $this->view($data, Codes::HTTP_OK)
        );
    }

    /**
     * Create new Customer
     *
     * @ApiDoc(
     *     description="Create a new Customer via the Api",
     *     resource=true
     * )
     * @AclAncestor("marello_customer_create")
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
        return $this->get('marello_order.manager.customer.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('marello_order.form.handler.customer_api');
    }
}
