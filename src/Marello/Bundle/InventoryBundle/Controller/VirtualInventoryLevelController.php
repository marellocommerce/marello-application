<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Oro\Bundle\SecurityBundle\Annotation as Security;

/**
 * @Config\Route("/virtual-inventory-level")
 */
class VirtualInventoryLevelController extends Controller
{
    /**
     * @Config\Route("/", name="marello_inventory_virtualinventorylevel_index")
     * @Security\AclAncestor("marello_inventory_inventory_view")
     * @Config\Template
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => VirtualInventoryLevel::class,
        ];

    }
}
