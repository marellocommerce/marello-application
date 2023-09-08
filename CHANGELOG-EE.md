- [InventoryBundle](#inventorybundle)
- [LayoutBundle](#layoutbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [ReplenishmentBundle](#replenishmentbundle)

InventoryBundle
-----
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid\InventoryLevelLogGridListenerTest::testOnBuildBefore` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Datagrid\InventoryLevelLogGridListenerTest::testOnBuildBeforeNoColumnAdded` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::__construct` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::getShippingAddress` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$configManager` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::setAclHelper` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::$aclHelper` [protected] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::onSendTransitionAfter` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::getEntityManager` [protected] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::$em` [protected] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\ConsolidationEnabledType` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Extension\ReshipmentExtension` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$strategiesRegistry` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$rulesFiltrationService` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$baseAllocationProvider` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::$doctrineHelper` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::$workflowManager` [protected] Property has been removed.

LayoutBundle
-----
* `MarelloEnterprise\Bundle\LayoutBundle\Form\Extension\FormChangesExtension::getExtendedTypes` [public] Method implementation changed.

PurchaseOrderBundle
-----
* `MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Extension\PurchaseOrderWarehouseFormExtension::buildForm` [public] Method implementation changed.

ReplenishmentBundle
-----
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::executeAction` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::getQuantities` [protected] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider::getReplenishmentOrders` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::up` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::createMarelloReplenishmentOrderConfigTable` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::createMarelloReplenishmentOrderTable` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::createMarelloReplenishmentOrderItemTable` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::addMarelloReplenishmentOrderForeignKeys` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::addMarelloReplenishmentOrderItemForeignKeys` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::createMarelloReplenishmentOrderManualItemTable` [protected] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\MarelloEnterpriseReplenishmentBundleInstaller::addMarelloReplenishmentOrderManualItemConfigForeignKeys` [protected] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigType::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::__construct` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::process` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::process` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::postUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::collectInventoryBatches` [private] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::__construct` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::getManualItems` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::addManualItem` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::removeManualItem` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::$manualItems` [protected] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem::isAllQuantity` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem::setAllQuantity` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem::$allQuantity` [protected] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::createAction` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::createStepTwoAction` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Tests\Functional\Controller\ReplenishmentOrderConfigAjaxControllerTest` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Tests\Functional\Controller\ReplenishmentOrderConfigControllerTest` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ManualReplenishmentStrategy` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentManualItemFormChangesProvider` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrderManualItemConfig` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ReplenishmentOrderStepOne` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\v2_1\MarelloReplenishmentBundle` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigManualType` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderManualItemCollectionType` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderManualItemType` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderStepOneType` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderStepOneHandler` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Datagrid\ReplenishmentOrderItemInventoryBatchesColumnListener` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderManualItemConfig` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigAjaxController` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::$replenishmentOrdersProvider` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::$registry` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::getFormView` [public] Method has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::__construct` [public] Method parameter removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::process` [public] Method parameter added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::process` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::process` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::$form` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::$request` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::$manager` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler::$replenishmentOrdersProvider` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder::getPercentage` [public] Method has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder::setPercentage` [public] Method has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder::$percentage` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method has been removed.


