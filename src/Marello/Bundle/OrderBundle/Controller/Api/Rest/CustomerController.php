<?php

namespace Marello\Bundle\OrderBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SecurityBundle\Annotation as Security;

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
     * REST GET Customer
     *
     * @param string $email
     *
     * @ApiDoc(
     *     description="Get Customer by email if one exists",
     *     resource=true
     * )
     * @Rest\Get("/customers/getcustomerbyemail/{email}")
     *
     * @return Response
     */
    public function getByEmailAction($email)
    {
        //todo:: prevent any kind of malicious code execution once the email is decoded...
        $email = base64_decode($email);

        var_dump($email);
        die(__METHOD__);
        if (!$email) {
            return $this->handleView(
                $this->view(
                    [
                        'message' => 'Customer email should be provided as parameter in request'
                    ],
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        $entity = $this->getDoctrine()
            ->getRepository('MarelloOrderBundle:Customer')
            ->findOneBy(['email' => $email]);

        if (!$entity) {
            return $this->handleView(
                $this->view(
                    [
                        'message' => sprintf(
                            'Customer with email %s not found',
                            $email
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
