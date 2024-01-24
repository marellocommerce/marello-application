## 5.0.0 (2024-01-23)

- [AddressBundle](#addressbundle)
- [InventoryBundle](#inventorybundle)
- [OrderBundle](#orderbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [ReplenishmentBundle](#replenishmentbundle)
- [SalesBundle](#salesbundle)

AddressBundle
-----
* `MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\EventListener\Doctrine\AddressGeocodingListenerTest::testPostUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\EventListener\Doctrine\AddressGeocodingListener::postUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\DependencyInjection\MarelloEnterpriseAddressExtension::load` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\AddressBundle\Model\ExtendMarelloEnterpriseAddress` Class was added.
* `MarelloEnterprise\Bundle\AddressBundle\EventListener\Doctrine\AddressGeocodingListener::postUpdate` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\AddressBundle\EventListener\Doctrine\AddressGeocodingListener::postUpdate` [public] Method parameter typing removed.

InventoryBundle
-----
* `MarelloEnterprise\Bundle\InventoryBundle\Twig\WarehouseExtension::getFunctions` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Twig\WarehouseExtension::getExpectedInventoryTotal` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseGroupREmoveListenerTest::testPreRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseListenerTest::testPrePersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\InventoryBundleInstaller::updateMarelloWarehouseTable` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\v1_1\AddIsConsolidationWarehouseColumn::updateMarelloWarehouseTable` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager::getExpectedInventoryTotal` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\AllocationConsolidationExclusionSelectType::getName` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseChannelGroupLinkType::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::buildView` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method parameter name changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType::assignDataToWarehouses` [private] Method has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType::postSubmitDataListener` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Extension\WarehouseExtension::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\DefaultWarehouseSubscriber::preUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\DefaultWarehouseSubscriber::prePersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\OrganizationCreateListener::__construct` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::onFlush` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::onFlush` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::prePersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\ExpectedInventoryItemControllerTest` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Model\ExtendWFARule` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid\ExpectedInventoryItemGridListener` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\ExpectedInventoryItemController` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType::getWarehouseCollection` [protected] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\DefaultWarehouseSubscriber::prePersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\DefaultWarehouseSubscriber::prePersist` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::preRemove` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::preRemove` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::prePersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::prePersist` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::preRemove` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::preRemove` [public] Method parameter typing removed.

OrderBundle
-----
* `MarelloEnterprise\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerDropshipmentTest::testAssigningOwnAndExternalWarehouse` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OnOrderCreateListener::onFlush` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::getApplicableWorkflow` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::getDefaultWorkflowNames` [protected] Method has been added.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::$baseListener` [private] Property has been added.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method parameter typing removed.

PurchaseOrderBundle
-----
* `MarelloEnterprise\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type\PurchaseOrderCreateStepTwoTypeTest::getExtensions` [public] Method implementation changed.

ReplenishmentBundle
-----
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::$doctrine` [protected] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::getQuantities` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderShipAction::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderShipAction::$doctrine` [protected] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\EqualDivision\EqualDivisionReplenishmentStrategy::getResults` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentManualItemFormChangesProvider::processFormChanges` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider::getReplenishmentOrders` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::postUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::collectInventoryBatches` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentWorkflowAllocateInventoryListener::postPersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentWorkflowAllocateInventoryListener::postFlush` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\DependencyInjection\MarelloEnterpriseReplenishmentExtension::load` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::execute` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::isActive` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::$doctrine` [private] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::$messageProducer` [private] Property has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor::getSubscribedTopics` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrder` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrderConfig` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrderManualItemConfig` Class was added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Async\Topic\AllocateReplenishmentOrdersInventoryTopic` Class was removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderShipAction::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderShipAction::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::postUpdate` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::postUpdate` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentWorkflowAllocateInventoryListener::postPersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentWorkflowAllocateInventoryListener::postPersist` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Command\AllocateDelayedReplenishmentOrdersInventoryCommand::__construct` [public] Method parameter typing removed.

SalesBundle
-----
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\OrganizationCreateListener::__construct` [public] Method has been added.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\OrganizationCreateListener::postPersist` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method parameter typing removed.


