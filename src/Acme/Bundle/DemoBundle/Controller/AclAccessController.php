<?php
namespace Acme\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Route("/acl")
 * @Acl(
 *      id = "acme_demo_test_controller",
 *      name="Test controller",
 *      description = "Test controller for ACL"
 * )
 */
class AclAccessController extends Controller
{
    /**
     * @Acl(
     *      id = "acme_demo_test_controller_role_user",
     *      name="Action for ROLE_USER",
     *      description = "Action for ROLE_USER",
     *      parent = "acme_demo_test_controller"
     * )
     * @Route("/users", name="acme_demo_acl_users_only")
     */
    public function usersEnabledAction()
    {
        return  new Response('Action for ROLE_USER');
    }

    /**
     * @Acl(
     *      id = "acme_demo_test_controller_role_manager",
     *      name="Action for ROLE_MANAGER",
     *      description = "Action for ROLE_MANAGER",
     *      parent = "acme_demo_test_controller"
     * )
     * @Route("/manager", name="acme_demo_acl_manager_only")
     */
    public function managerEnabledAction()
    {
        return  new Response('Action for ROLE_MANAGER');
    }

    /**
     * @Acl(
     *      id = "acme_demo_test_controller_role_admin",
     *      name="Action for ROLE_ADMIN",
     *      description = "Action for ROLE_ADMIN",
     *      parent = "acme_demo_test_controller"
     * )
     * @Route("/admin", name="acme_demo_acl_manager_only")
     */
    public function adminEnabledAction()
    {
        return  new Response('Action for ROLE_ADMIN');
    }
}
