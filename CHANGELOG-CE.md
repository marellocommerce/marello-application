## 5.0.0 (2024-01-23)

- [AddressBundle](#addressbundle)
- [BankTransferBundle](#banktransferbundle)
- [CatalogBundle](#catalogbundle)
- [CoreBundle](#corebundle)
- [CustomerBundle](#customerbundle)
- [DataGridBundle](#datagridbundle)
- [DemoDataBundle](#demodatabundle)
- [FilterBundle](#filterbundle)
- [InventoryBundle](#inventorybundle)
- [InvoiceBundle](#invoicebundle)
- [ManualShippingBundle](#manualshippingbundle)
- [NotificationBundle](#notificationbundle)
- [NotificationMessageBundle](#notificationmessagebundle)
- [OrderBundle](#orderbundle)
- [POSBundle](#posbundle)
- [PackingBundle](#packingbundle)
- [PaymentBundle](#paymentbundle)
- [PaymentTermBundle](#paymenttermbundle)
- [PdfBundle](#pdfbundle)
- [PricingBundle](#pricingbundle)
- [ProductBundle](#productbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [RefundBundle](#refundbundle)
- [ReturnBundle](#returnbundle)
- [RuleBundle](#rulebundle)
- [SalesBundle](#salesbundle)
- [ShippingBundle](#shippingbundle)
- [SupplierBundle](#supplierbundle)
- [TaxBundle](#taxbundle)
- [UPSBundle](#upsbundle)
- [WebhookBundle](#webhookbundle)
- [WorkflowBundle](#workflowbundle)

AddressBundle
-----
* `Marello\Bundle\AddressBundle\DependencyInjection\MarelloAddressExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\AddressBundle\Model\ExtendMarelloAddress` Class was added.

BankTransferBundle
-----

CatalogBundle
-----
* `Marello\Bundle\CatalogBundle\Entity\Category::__construct` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\DependencyInjection\MarelloCatalogExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\CatalogBundle\Model\ExtendCategory` Class was added.
* `Marello\Bundle\CatalogBundle\Twig\CategoryExtension::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CatalogBundle\Twig\CategoryExtension::__construct` [public] Method parameter typing removed.

CoreBundle
-----
* `Marello\Bundle\CoreBundle\MarelloCoreBundle::build` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanOrEqualToValueValidator::__construct` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanOrEqualToValueValidator::$registry` [private] Property has been added.
* `Marello\Bundle\CoreBundle\Validator\UniqueEntityCollectionValidator::__construct` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Validator\UniqueEntityCollectionValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Validator\UniqueEntityCollectionValidator::$registry` [private] Property has been added.
* `Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler::$managerRegistry` [protected] Property has been added.
* `Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler::$entityClass` [protected] Property has been added.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::isAssociation` [private] Method has been removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::getIdFieldNameIfIdOnlyRequested` [private] Method has been removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::__construct` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::serializeItem` [protected] Method implementation changed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::$configAccessor` [private] Property has been removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::$fieldFilter` [private] Property has been removed.
* `Marello\Bundle\CoreBundle\Form\UrlGenerator::getUrl` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Form\UrlGenerator::findEditionAndVersionInPackage` [private] Method implementation changed.
* `Marello\Bundle\CoreBundle\EventListener\EmailAddressVisibilityListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\EventListener\RefreshContextListener::checkOrganization` [protected] Method implementation changed.
* `Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetter::onFlush` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetter::postFlush` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\DependencyInjection\MarelloCoreExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\CoreBundle\Mailer\Processor` Class was added.
* `Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\OroEmailProcessorOverrideServiceCompilerPass` Class was added.
* `Marello\Bundle\CoreBundle\Provider\AttachmentEntityConfigProviderDecorator` Class was removed.
* `Marello\Bundle\CoreBundle\EmbeddedImages\EmbeddedImagesExtractor` Class was removed.
* `Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\OroEmailImagesExtractorOverrideServiceCompilerPass` Class was removed.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanDateValidator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanDateValidator::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanOrEqualToValueValidator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Validator\GreaterThanOrEqualToValueValidator::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Validator\UniqueEntityCollectionValidator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Validator\UniqueEntityCollectionValidator::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Tree\Handler\AbstractTreeHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::setFieldFilter` [public] Method has been removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Serializer\EntitySerializer::serializeItem` [protected] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Form\UrlGenerator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CoreBundle\Form\UrlGenerator::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait::prePersistTimestamp` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Migration\UpdateExtendRelationTrait::migrateConfig` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait` Trait was removed.

CustomerBundle
-----
* `Marello\Bundle\CustomerBundle\Tests\Unit\Form\Type\ParentCompanySelectTypeTest::buildViewDataProvider` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testCreateNewCustomer` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testCreateNewCustomerWithoutAddress` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testGetCustomerFilteredByEmail` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller::createMarelloCustomerTable` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Form\Type\CompanySelectType::getName` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Form\Type\CompanyType::getName` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Form\Type\ParentCompanySelectType::getName` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::__construct` [public] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::process` [public] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::process` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::$form` [protected] Property has been added.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::$request` [protected] Property has been added.
* `Marello\Bundle\CustomerBundle\Entity\Company::__construct` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::createAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter default added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::getSubscribedServices` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Model\ExtendCompany` Class was added.
* `Marello\Bundle\CustomerBundle\Model\ExtendCustomer` Class was added.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5_1\MarelloCustomerBundle` Class was removed.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::findEmailOwner` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::findEmailOwner` [public] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getOrganizations` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getOrganizations` [public] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getEmails` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getEmails` [public] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::__construct` [public] Method parameter added.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::process` [public] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler::process` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::createAction` [public] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter typing removed.

DataGridBundle
-----
* `Marello\Bundle\DataGridBundle\Extension\Totals\OrmTotalsExtension::applyFrontendFormatting` [protected] Method implementation changed.
* `Marello\Bundle\DataGridBundle\DependencyInjection\MarelloDataGridExtension::load` [public] Method parameter name changed.

DemoDataBundle
-----
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPaymentRule::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadShippingRule::getIdentifier` [private] Method has been removed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadShippingRule::load` [public] Method implementation changed.

FilterBundle
-----
* `Marello\Bundle\FilterBundle\DependencyInjection\MarelloFilterExtension::load` [public] Method parameter name changed.

InventoryBundle
-----
* `Marello\Bundle\InventoryBundle\Validator\InventoryOrderOnDemandValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::getFunctions` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest::testGetWarehouseResults` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Controller\InventoryLogLevelControllerTest::testShowInventoryLogList` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Controller\InventoryLogLevelControllerTest::testInventoryItemViewLogRecordGrid` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Api\BalancedInventoryJsonApiTest::testFilterInventoryLevelBySalesChannel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::__construct` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getWarehouseResults` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getInventoryLevelCandidates` [protected] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getInventoryLevelCandidates` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::isWarehouseEligible` [protected] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::isWarehouseEligible` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::isItemAvailable` [protected] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::isItemAvailable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getLinkedWarehouses` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$minQtyWHCalculator` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$warehouseChannelGroupLinkRepository` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$configManager` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\AbstractWHCalculator::getExternalWarehouseData` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::createAllocationItems` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::handleAllocationInventory` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator::calculateBatchInventoryLevelQty` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryInventoryBatchTable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryWarehouseTable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData::loadDefaultWarehouse` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Reader\InventoryLevelReader::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Reader\InventoryLevelReader::$registry` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryBatchType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryItemType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryItemType::preSetDataListener` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::getSubscribedEvents` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::handleUnMappedFields` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::$levelCalculator` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::$eventDispatcher` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::handleInventoryUpdateAfterEvent` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryItemEventListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryItemEventListener::triggerRebalance` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener::handleUpdateInventoryEvent` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener::$manager` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener::$balancedInventoryManager` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\EventListener\OnProductCreateEventListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\OnProductDeleteEventListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::onAllocationComplete` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::getDefaultWorkflowNames` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::onPendingTransitionAfter` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryBatchEventListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryBatchInventoryRebalanceListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryBatchInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelAuthorFillSubscriber::prePersist` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectAssignSubscriber::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectAssignSubscriber::postFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectHydrationSubscriber::postLoad` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Allocation::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryItem::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\WarehouseGroup::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\DependencyInjection\MarelloInventoryExtension::load` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\BalancedInventoryLevelController::recalculateAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::triggerInventoryBalancer` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::$registry` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::$inventoryBalancer` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::$messageProducer` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Command\InventoryReAllocateCronCommand::updateAllocationWorkflow` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryReAllocateCronCommand::isActive` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Async\BalancedInventoryResetProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::$logger` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::$inventoryBalancer` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::$registry` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductsProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductsProcessor::process` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Model\ExtendAllocation` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendAllocationItem` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendInventoryBatch` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendInventoryItem` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendInventoryLevel` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendWarehouse` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendWarehouseChannelGroupLink` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendWarehouseGroup` Class was added.
* `Marello\Bundle\InventoryBundle\Async\Topics` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Controller\ExpectedInventoryItemControllerTest` Class was removed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_10\MarelloInventoryBundle` Class was removed.
* `Marello\Bundle\InventoryBundle\EventListener\Datagrid\ExpectedInventoryForAllocationItemsGridListener` Class was removed.
* `Marello\Bundle\InventoryBundle\EventListener\Datagrid\ExpectedInventoryItemGridListener` Class was removed.
* `Marello\Bundle\InventoryBundle\Event\InventoryUpdateWebhookEvent` Class was removed.
* `Marello\Bundle\InventoryBundle\Controller\ExpectedInventoryItemController` Class was removed.
* `Marello\Bundle\InventoryBundle\Command\InventorySellByDateRecalculateCronCommand` Class was removed.
* `Marello\Bundle\InventoryBundle\Async\Topic\BalancedInventoryResetTopic` Class was removed.
* `Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceAllInventoryTopic` Class was removed.
* `Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic` Class was removed.
* `Marello\Bundle\InventoryBundle\Api\Processor\ComputeSalesChannelsField` Class was removed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::getExpectedInventoryTotal` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::getExpiredSellByDateTotal` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method parameter removed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getInventoryLevelCandidates` [protected] Method parameter removed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getInventoryLevelCandidates` [protected] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::handleInventoryUpdate` [protected] Method parameter removed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$isCashAndCarryAllocation` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::getExpectedInventoryTotal` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::getExpiredSellByDateTotal` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\ImportExport\Reader\InventoryLevelReader::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\ImportExport\Reader\InventoryLevelReader::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::storeOldData` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::__construct` [public] Method parameter removed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener::__construct` [public] Method parameter removed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelAuthorFillSubscriber::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelAuthorFillSubscriber::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectHydrationSubscriber::postLoad` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\StockLevelSubjectHydrationSubscriber::postLoad` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::getSellByDate` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::setSellByDate` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::getOrderOnDemandRef` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::setOrderOnDemandRef` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::$sellByDate` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryBatch::$orderOnDemandRef` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::addInventoryLevelLogRecord` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::removeInventoryLevelLogRecord` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::getInventoryLevelLogRecords` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::$inventoryLevelLogRecords` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::getSortOrderOodLoc` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::setSortOrderOodLoc` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::isOrderOnDemandLocation` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::setOrderOnDemandLocation` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::$sortOrderOodLoc` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::$orderOnDemandLocation` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository::findWithExpiredSellByDateBatch` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Async\InventoryRebalanceProductProcessor::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Strategy\BalancerStrategyInterface::getResults` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Model\InventoryItemAwareInterface::getInventoryItem` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Model\InventoryItemAwareInterface::getInventoryItems` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\SetsPropertyValue::setPropertyValue` [protected] Method implementation changed.

InvoiceBundle
-----
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::$doctrine` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::$translator` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::$parametersProvider` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::$renderer` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::$doctrine` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::$translator` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::$parametersProvider` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::$renderer` [private] Property has been added.
* `Marello\Bundle\InvoiceBundle\Model\ExtendCreditmemo` Class was added.
* `Marello\Bundle\InvoiceBundle\Model\ExtendCreditmemoItem` Class was added.
* `Marello\Bundle\InvoiceBundle\Model\ExtendInvoice` Class was added.
* `Marello\Bundle\InvoiceBundle\Model\ExtendInvoiceItem` Class was added.
* `Marello\Bundle\InvoiceBundle\Twig\InvoiceExtension::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InvoiceBundle\Twig\InvoiceExtension::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::__construct` [public] Method parameter typing removed.

ManualShippingBundle
-----
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::createDefaultShippingRule` [private] Method has been removed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::getDependencies` [public] Method implementation changed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::load` [public] Method implementation changed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::loadIntegration` [private] Method implementation changed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::addMethodConfigToDefaultShippingRule` [private] Method parameter name changed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::addMethodConfigToDefaultShippingRule` [private] Method parameter typing added.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::addMethodConfigToDefaultShippingRule` [private] Method parameter typing removed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::addMethodConfigToDefaultShippingRule` [private] Method implementation changed.

NotificationBundle
-----
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testSendsNotification` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Entity\Notification::__construct` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Model\ExtendNotification` Class was added.
* `Marello\Bundle\NotificationBundle\Provider\NotificationActivityListProvider::isActivityListApplicable` [public] Method has been removed.

NotificationMessageBundle
-----
* `Marello\Bundle\NotificationMessageBundle\Form\Type\NotificationMessageGroupConfigType::getName` [public] Method has been added.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createError` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createWarning` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createSuccess` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createInfo` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::create` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::processMessage` [private] Method has been removed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::__construct` [public] Method parameter name changed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::onCreate` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::onResolve` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::getEntityManager` [private] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::createNewNotificationMessage` [private] Method has been added.
* `Marello\Bundle\NotificationMessageBundle\Cron\NotificationMessageCleanupCommand::isActive` [public] Method has been added.
* `Marello\Bundle\NotificationMessageBundle\Model\ExtendNotificationMessage` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageFactory` Class was removed.
* `Marello\Bundle\NotificationMessageBundle\Async\ProcessNotificationMessageProcessor` Class was removed.
* `Marello\Bundle\NotificationMessageBundle\Async\Topic\ProcessNotificationMessageTopic` Class was removed.
* `Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext::$queue` [public] Property has been removed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createError` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createWarning` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createSuccess` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::createInfo` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory::create` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::__construct` [public] Method parameter removed.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener::__construct` [public] Method parameter typing removed.

OrderBundle
-----
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::$doctrine` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::$eventDispatcher` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::$doctrine` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::$eventDispatcher` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isProductCanDropship` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isProductCanBackorder` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::getBackorderQty` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isProductCanPreorder` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::getPreorderQty` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isOrderOnDemandAllowed` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Validator\AvailableInventoryValidatorTest::testValidateViolationIsBuild` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine\OrderWorkflowStartListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine\OrderWorkflowStartListenerTest::createEventArgsMock` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\EventListener\Doctrine\OrderWorkflowStartListenerTest::$doctrineHelperMock` [private] Property has been added.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::loadReturns` [private] Method has been removed.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::load` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderAjaxControllerTest::testFormChangesAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderOnDemandWorkflowTest::testWorkflow` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderTotalsProvider::getTotalWithSubtotalsValues` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::createMarelloOrderOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::createMarelloOrderOrderItemTable` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::addMarelloOrderOrderForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::addMarelloOrderOrderItemForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderItemType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber::onSubmit` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\OrderCreatedNotificationSender::derivedPropertySet` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Workflow\PurchaseOrderWorkflowCompletedListener::onPurchaseOrderCompleted` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderInventoryAllocationListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemProductUnitListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::$tokenStorage` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::getApplicableWorkflow` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::getDefaultWorkflowNames` [protected] Method has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::setDoctrineHelper` [public] Method has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::$doctrineHelper` [private] Property has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\PendingOrderStatusListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Order::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::getInventoryItems` [public] Method has been added.
* `Marello\Bundle\OrderBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderController::update` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Model\ExtendCustomer` Class was added.
* `Marello\Bundle\OrderBundle\Model\ExtendOrder` Class was added.
* `Marello\Bundle\OrderBundle\Model\ExtendOrderItem` Class was added.
* `Marello\Bundle\OrderBundle\Entity\Customer` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_5\MarelloOrderBundle` Class was removed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_4\MarelloOrderBundle` Class was removed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateSystemConfigValues` Class was removed.
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Twig\OrderExtension::setRegistry` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Twig\OrderExtension::setRegistry` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::__construct` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber::__construct` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Workflow\PurchaseOrderWorkflowCompletedListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Workflow\PurchaseOrderWorkflowCompletedListener::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderInventoryAllocationListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderInventoryAllocationListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemOriginalPriceListener::prePersist` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemProductUnitListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemProductUnitListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::__construct` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\PendingOrderStatusListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\PendingOrderStatusListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Entity\Order::setPaymentMethodOptions` [public] Method parameter default removed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::getInventoryItem` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::getItemType` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::setItemType` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::$itemType` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Model\OrderItemTypeInterface` Interface was removed.

POSBundle
-----
* `Marello\Bundle\POSBundle\MarelloPOSBundle` Class was removed.
* `Marello\Bundle\POSBundle\Tests\Functional\DataFixtures\LoadUserData` Class was removed.
* `Marello\Bundle\POSBundle\Tests\Functional\Api\POSUserApiTest` Class was removed.
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData` Class was removed.
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadSalesChannelPOSTypeData` Class was removed.
* `Marello\Bundle\POSBundle\EventListener\Workflow\PosOrderAllocationWorkflowListener` Class was removed.
* `Marello\Bundle\POSBundle\EventListener\Workflow\PosOrderWorkflowListener` Class was removed.
* `Marello\Bundle\POSBundle\EventListener\Doctrine\OnPOSOrderCreateListener` Class was removed.
* `Marello\Bundle\POSBundle\DependencyInjection\MarelloPOSExtension` Class was removed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin` Class was removed.
* `Marello\Bundle\POSBundle\Api\Model\Login` Class was removed.

PackingBundle
-----
* `Marello\Bundle\PackingBundle\Tests\Unit\Mapper\OrderToPackingSlipMapperTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\EventListener\Doctrine\PackingSlipItemStatusListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::__construct` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Model\ExtendPackingSlip` Class was added.
* `Marello\Bundle\PackingBundle\Model\ExtendPackingSlipItem` Class was added.
* `Marello\Bundle\PackingBundle\Pdf\Request\PackingSlipPdfRequestHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PackingBundle\Pdf\Request\PackingSlipPdfRequestHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PackingBundle\EventListener\Doctrine\PackingSlipItemStatusListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\PackingBundle\EventListener\Doctrine\PackingSlipItemStatusListener::prePersist` [public] Method parameter typing removed.

PaymentBundle
-----
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Provider\ChannelPaymentMethodProviderTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Provider\ChannelPaymentMethodProviderTest::createLifecycleEventArgsMock` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Condition\PaymentMethodHasPaymentRulesTest::testSetContextAccessor` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider::getPaymentMethods` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider::loadChannels` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType::$registry` [private] Property has been added.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType::$paymentMethodChoicesProvider` [private] Property has been added.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentUpdateType::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentUpdateType::$registry` [private] Property has been added.
* `Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRuleDestination::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Model\ExtendPayment` Class was added.
* `Marello\Bundle\PaymentBundle\Model\ExtendPaymentMethodConfig` Class was added.
* `Marello\Bundle\PaymentBundle\Model\ExtendPaymentMethodsConfigsRule` Class was added.
* `Marello\Bundle\PaymentBundle\Model\ExtendPaymentMethodsConfigsRuleDestination` Class was added.
* `Marello\Bundle\PaymentBundle\Model\ExtendPaymentMethodsConfigsRuleDestinationPostalCode` Class was added.
* `Marello\Bundle\PaymentBundle\Migrations\Data\ORM\CreateDefaultPaymentRule` Class was added.
* `Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider::postLoad` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentUpdateType::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PaymentBundle\Form\Type\PaymentUpdateType::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PaymentBundle\Entity\Payment::setPaymentMethodOptions` [public] Method parameter default removed.
* `Marello\Bundle\PaymentBundle\Entity\Payment::setPaymentReference` [public] Method parameter typing removed.
* `Marello\Bundle\PaymentBundle\Entity\Payment::setPaymentReference` [public] Method parameter default removed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Helper\PaymentTermIntegrationTrait::getReference` [public] Method parameter typing removed.

PaymentTermBundle
-----
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::createDefaltPaymentRule` [private] Method has been removed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::getDependencies` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::load` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::addMethodConfigToDefaultPaymentRule` [private] Method parameter name changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::addMethodConfigToDefaultPaymentRule` [private] Method parameter typing added.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::addMethodConfigToDefaultPaymentRule` [private] Method parameter typing removed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::addMethodConfigToDefaultPaymentRule` [private] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Model\ExtendPaymentTerm` Class was added.

PdfBundle
-----
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo\LogoRenderParameterProviderTest::testSupportsOptions` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo\LogoRenderParameterProviderTest::testSupportsUnsupported` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo\LogoRenderParameterProviderTest::getParamsProvider` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::$config` [protected] Property has been added.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::$doctrine` [protected] Property has been added.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::$localizationParameterName` [protected] Property has been added.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PdfBundle\Provider\Render\LocalizationProvider::__construct` [public] Method parameter typing removed.

PricingBundle
-----
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetListOfAssembledChannelPriceLists` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetPriceListByProductSku` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetPriceListByChannel` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testCreateNewPriceListWithPrices` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledPriceListJsonApiTest::testCreateNewPriceListWithDefaultPrice` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Subtotal\Provider\CompositeSubtotalProvider::getTotal` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Provider\CurrencyProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Provider\CurrencyProvider::$registry` [protected] Property has been added.
* `Marello\Bundle\PricingBundle\Provider\CurrencyProvider::$localeSettings` [protected] Property has been added.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::__construct` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::$provider` [protected] Property has been added.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::$doctrine` [protected] Property has been added.
* `Marello\Bundle\PricingBundle\Model\ExtendAssembledChannelPriceList` Class was added.
* `Marello\Bundle\PricingBundle\Model\ExtendAssembledPriceList` Class was added.
* `Marello\Bundle\PricingBundle\Tests\Unit\Subtotal\Provider\CompositeSubtotalProviderTest::testGetTotalWithProvidedSubtotal` [public] Method has been removed.
* `Marello\Bundle\PricingBundle\Subtotal\Provider\CompositeSubtotalProvider::getTotal` [public] Method parameter removed.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PricingBundle\Provider\CurrencyProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PricingBundle\Provider\CurrencyProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PricingBundle\Subtotal\Provider\TotalAwareSubtotalProviderInterface::getTotal` [public] Method parameter removed.

ProductBundle
-----
* `Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecorator::getPropertyAccessor` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Validator\ProductSupplierRelationsDropshipValidator::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Validator\ProductSupplierRelationsDropshipValidator::$doctrine` [private] Property has been added.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest::testMediaUrlUpdated` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest::testNoInsertionsOrUpdates` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Controller\ProductControllerTest::testCreateProduct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetProductById` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetListOfProducts` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetProductFilteredBySku` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testCreateNewProduct` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testUpdateProduct` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Provider\OrderItemProductUnitProvider::processFormChanges` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::addImageRelation` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_14\UpdateAttachmentFileTable::addAdditionalMediaUrl` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Handler\ProductHandler::onSuccess` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Handler\ProductsSalesChannelsAssignHandler::sendProductsToMessageQueue` [private] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\ProductImageListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\ProductImageListener::getProductsToProcess` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\ProductImageListener::sendToMessageProducer` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::preUpdate` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductUpdateInventoryRebalanceListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductUpdateInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductUpdateInventoryRebalanceListener::triggerBalancedInventoryReset` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::__clone` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::prePersist` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::preUpdate` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::getInventoryItems` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::addInventoryItem` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::removeInventoryItem` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::setCreatedAt` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::getCreatedAt` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::setUpdatedAt` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::getUpdatedAt` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::$inventoryItems` [protected] Property has been added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Manager\ProductApiEntityManager::getSerializationConfig` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\DependencyInjection\MarelloProductExtension::load` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::update` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\VariantController::updateVariant` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Async\ProductImageUpdateProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadAllocationContextData::getVersion` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Model\ExtendProduct` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_6\UpdateEntityConfigDataQuery` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_6\UpdateEntityConfigQuery` Class was removed.
* `Marello\Bundle\ProductBundle\Async\Topic\ProductImageUpdateTopic` Class was removed.
* `Marello\Bundle\ProductBundle\Async\Topic\ProductsAssignSalesChannelsTopic` Class was removed.
* `Marello\Bundle\ProductBundle\Validator\ProductSupplierRelationsDropshipValidator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Validator\ProductSupplierRelationsDropshipValidator::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest::testNoImageEntityUpdates` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest::testNoImageCreation` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest::testImageCreation` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::preRemove` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::preRemove` [public] Method parameter typing removed.
* `Marello\Bundle\ProductBundle\Entity\Product::getInventoryItem` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::setInventoryItem` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::updateDenormalizedProperties` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::$inventoryItem` [protected] Property has been removed.

PurchaseOrderBundle
-----
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::executeAction` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::initialize` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::$manager` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::$noteActivityProcessor` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::callMethod` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::testExecutePartial` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::testExecuteOneItemPartial` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\TransitCompleteActionTest::callMethod` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type\PurchaseOrderItemTypeTest::getExtensions` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::testPostFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::getAllocation` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::getProduct` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::getWarehouseChannelGroupLing` [private] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::$purchaseOrderOnOrderOnDemandCreationListener` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::testAdviceCommandWillNotRunBecauseFeatureIsNotEnabled` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::createPurchaseOrdersFromAllocation` [private] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::updatePurchaseOrdersTotal` [private] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::createInventoryBatch` [private] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::getOnDemandLocation` [private] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::isOrderOnDemandItem` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::getLinkedWarehouse` [private] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::setConfigManager` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::$configManager` [private] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::isOrderOnDemandAllowed` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::transitTo` [private] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::getCurrentWorkFlowItem` [private] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::$entitiesScheduledForWorkflowStart` [private] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::$workflowManager` [private] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::$purchaseOrderId` [private] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder::usingProducts` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem::getInventoryItems` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::execute` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::setEmailTemplateManager` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::setNotificationSettings` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::$notificationSettings` [private] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::$emailTemplateManager` [private] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::update` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Model\ExtendPurchaseOrder` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\Model\PurchaseOrder` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid\PurchaseOrderGridListener` Class was removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::setPickupLocation` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::getEnumValue` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::updateAllocationWorkflow` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::__construct` [public] Method parameter removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::$pickupLocation` [protected] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::$listener` [protected] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateHandler::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateHandler::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::createNotificationContext` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::getApplicableWorkflow` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::getDefaultWorkflowNames` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::__construct` [public] Method parameter removed.

RefundBundle
-----
* `Marello\Bundle\RefundBundle\Workflow\Actions\CreateRefundAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Workflow\Actions\CreateRefundAction::$doctrine` [protected] Property has been added.
* `Marello\Bundle\RefundBundle\Entity\Refund::__construct` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\DependencyInjection\MarelloRefundExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\RefundBundle\Controller\RefundController::update` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::__construct` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::$doctrine` [protected] Property has been added.
* `Marello\Bundle\RefundBundle\Model\ExtendRefund` Class was added.
* `Marello\Bundle\RefundBundle\Workflow\Actions\CreateRefundAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\RefundBundle\Workflow\Actions\CreateRefundAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::__construct` [public] Method parameter typing removed.

ReturnBundle
-----
* `Marello\Bundle\ReturnBundle\Tests\Functional\Controller\ReturnControllerTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Tests\Functional\Api\ReturnJsonApiTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloProductWarranty::getEnumvalueById` [private] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloRorWarranty::getEnumvalueById` [private] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Entity\ReturnItem::getInventoryItems` [public] Method has been added.
* `Marello\Bundle\ReturnBundle\DependencyInjection\MarelloReturnExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\ReturnBundle\DependencyInjection\MarelloReturnExtension::load` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::createAction` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Model\ExtendReturnEntity` Class was added.
* `Marello\Bundle\ReturnBundle\Model\ExtendReturnItem` Class was added.
* `Marello\Bundle\ReturnBundle\Entity\ReturnItem::getInventoryItem` [public] Method has been removed.

RuleBundle
-----
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::__construct` [public] Method parameter name changed.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::__construct` [public] Method implementation changed.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::validate` [public] Method parameter name changed.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::$expressionParser` [private] Property has been removed.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::$basicExpressionLanguageValidator` [private] Property has been added.
* `Marello\Bundle\RuleBundle\Tests\Unit\Form\Type\RuleTypeTest::submitDataProvider` [public] Method implementation changed.
* `Marello\Bundle\RuleBundle\Model\ExtendRule` Class was added.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::__construct` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Validator\Constraints\ExpressionLanguageSyntaxValidator::__construct` [public] Method parameter typing removed.

SalesBundle
-----
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::getFunctions` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\Twig\SalesExtensionTest::testGetFunctions` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::testPreRemove` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::testPostPersist` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelListenerTest::testPrePersist` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Provider\SalesChannelConfigurationFormProvider::getTree` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Provider\SalesChannelConfigurationFormProvider::getJsTree` [public] Method has been added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::rebalanceForSalesChannelGroup` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::getSystemWarehouseChannelGroupLink` [private] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\DependencyInjection\MarelloSalesExtension::load` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\ConfigController::salesChannelAction` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Async\RebalanceSalesChannelGroupProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Async\RebalanceSalesChannelGroupProcessor::process` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Model\ExtendSalesChannel` Class was added.
* `Marello\Bundle\SalesBundle\Model\ExtendSalesChannelGroup` Class was added.
* `Marello\Bundle\SalesBundle\Async\Topics` Class was added.
* `Marello\Bundle\SalesBundle\Async\Topic\RebalanceSalesChannelGroupTopic` Class was removed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::getProductIdsByChannelIds` [public] Method has been removed.
* `Marello\Bundle\SalesBundle\Provider\SalesChannelConfigurationFormProvider::getTreeName` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\Config\SalesChannelScopeManager::setScopeId` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\Config\SalesChannelScopeManager::isSupportedScopeEntity` [protected] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\Config\SalesChannelScopeManager::getScopeEntityIdValue` [protected] Method parameter typing removed.

ShippingBundle
-----
* `Marello\Bundle\ShippingBundle\Validator\Constraints\EnabledTypeConfigsValidationGroupValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Provider\Integration\ChannelShippingMethodProviderTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Provider\Integration\ChannelShippingMethodProviderTest::createLifecycleEventArgsMock` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Condition\ShippingMethodHasShippingRulesTest::testSetContextAccessor` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS\UPSShippingServiceDataFactoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS\UPSShippingServiceDataFactoryTest::testReturnShipment` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceDataFactoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceDataFactoryTest::testReturnShipment` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceIntegrationTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceIntegrationTest::testIntegrationReturn` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::getPrice` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::hasPrice` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::savePrice` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::deleteAllPrices` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider::getShippingMethods` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider::loadChannels` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::__construct` [public] Method parameter name changed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::prepareLabelFile` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::$api` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::$shipmentConfirmRequestBuilder` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::$doctrine` [public] Property has been added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::$shipmentAcceptRequestBuilder` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::$attachmentManager` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceIntegration::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceIntegration::$doctrine` [public] Property has been added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::isShippingRule` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Context\ShippingContextCacheKeyGenerator::generateKey` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCacheTest` Class was added.
* `Marello\Bundle\ShippingBundle\Model\ExtendShipment` Class was added.
* `Marello\Bundle\ShippingBundle\Model\ExtendShippingMethodConfig` Class was added.
* `Marello\Bundle\ShippingBundle\Model\ExtendShippingMethodTypeConfig` Class was added.
* `Marello\Bundle\ShippingBundle\Model\ExtendShippingMethodsConfigsRule` Class was added.
* `Marello\Bundle\ShippingBundle\Model\ExtendTrackingInfo` Class was added.
* `Marello\Bundle\ShippingBundle\Migrations\Data\ORM\CreateDefaultShippingRule` Class was added.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Cache\ShippingPriceCacheTest` Class was removed.
* `Marello\Bundle\ShippingBundle\Workflow\ShipmentCreateAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Workflow\ShipmentCreateAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider::postLoad` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider::postLoad` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceIntegration::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceIntegration::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postUpdate` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postUpdate` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postRemove` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::postRemove` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::isShippingRule` [protected] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::isShippingRule` [protected] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::invalidateCache` [protected] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\EventListener\Cache\ShippingRuleChangeListener::invalidateCache` [protected] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Helper\ManualShippingIntegrationTrait::getReference` [public] Method parameter typing removed.

SupplierBundle
-----
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preUpdate` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\DependencyInjection\MarelloSupplierExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\SupplierBundle\Model\ExtendSupplier` Class was added.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::prePersist` [public] Method parameter typing added.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::prePersist` [public] Method parameter typing removed.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preRemove` [public] Method parameter typing added.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preRemove` [public] Method parameter typing removed.

TaxBundle
-----
* `Marello\Bundle\TaxBundle\DependencyInjection\MarelloTaxExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Model\ExtendTaxCode` Class was added.

UPSBundle
-----
* `Marello\Bundle\UPSBundle\Tests\Unit\Form\Type\UPSTransportSettingsTypeTest::getExtensions` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::testFetchPrice` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::testFetchPriceFalse` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::testContainsPrice` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::testContainsPriceFalse` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Cache\ShippingPriceCacheTest::testSavePrice` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Migrations\Data\ORM\MoveConfigValuesToSettings::getDependencies` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Migrations\Data\ORM\MoveConfigValuesToSettings::addMethodConfigToDefaultShippingRule` [private] Method implementation changed.
* `Marello\Bundle\UPSBundle\EventListener\UPSTransportEntityListener::postUpdate` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::containsPriceByStringKey` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::fetchPrice` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::savePrice` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::deleteAll` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::setNamespace` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigToSettingsConverter::__construct` [public] Method parameter typing added.
* `Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigToSettingsConverter::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\UPSBundle\EventListener\UPSTransportEntityListener::postUpdate` [public] Method parameter typing added.
* `Marello\Bundle\UPSBundle\EventListener\UPSTransportEntityListener::postUpdate` [public] Method parameter typing removed.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::__construct` [public] Method parameter typing added.
* `Marello\Bundle\UPSBundle\Cache\ShippingPriceCache::__construct` [public] Method parameter typing removed.

WebhookBundle
-----
* `Marello\Bundle\WebhookBundle\MarelloWebhookBundle` Class was removed.
* `Marello\Bundle\WebhookBundle\Twig\WebhookExtension` Class was removed.
* `Marello\Bundle\WebhookBundle\Tests\Unit\Entity\WebhookTest` Class was removed.
* `Marello\Bundle\WebhookBundle\Tests\Functional\Controller\WebhookControllerTest` Class was removed.
* `Marello\Bundle\WebhookBundle\Model\WebhookContext` Class was removed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller` Class was removed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\v1_0\MarelloWebhookBundle` Class was removed.
* `Marello\Bundle\WebhookBundle\Manager\WebhookProducer` Class was removed.
* `Marello\Bundle\WebhookBundle\Manager\WebhookProvider` Class was removed.
* `Marello\Bundle\WebhookBundle\Integration\WebhookChannel` Class was removed.
* `Marello\Bundle\WebhookBundle\Integration\Transport\WebhookTransport` Class was removed.
* `Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector` Class was removed.
* `Marello\Bundle\WebhookBundle\ImportExport\Writer\WebhookExportWriter` Class was removed.
* `Marello\Bundle\WebhookBundle\ImportExport\Processor\AsyncProcessor` Class was removed.
* `Marello\Bundle\WebhookBundle\Form\Type\EventSelectType` Class was removed.
* `Marello\Bundle\WebhookBundle\Form\Type\WebhookSettingsType` Class was removed.
* `Marello\Bundle\WebhookBundle\Form\Type\WebhookType` Class was removed.
* `Marello\Bundle\WebhookBundle\Event\AbstractWebhookEvent` Class was removed.
* `Marello\Bundle\WebhookBundle\Event\Provider\WebhookEventProvider` Class was removed.
* `Marello\Bundle\WebhookBundle\Entity\Webhook` Class was removed.
* `Marello\Bundle\WebhookBundle\Entity\WebhookNotificationSettings` Class was removed.
* `Marello\Bundle\WebhookBundle\Entity\Repository\WebhookRepository` Class was removed.
* `Marello\Bundle\WebhookBundle\DependencyInjection\Configuration` Class was removed.
* `Marello\Bundle\WebhookBundle\DependencyInjection\MarelloWebhookExtension` Class was removed.
* `Marello\Bundle\WebhookBundle\Controller\WebhookController` Class was removed.
* `Marello\Bundle\WebhookBundle\Async\WebhookSyncProcessor` Class was removed.
* `Marello\Bundle\WebhookBundle\Async\Topic\WebhookSyncTopic` Class was removed.
* `Marello\Bundle\WebhookBundle\Event\WebhookEventInterface` Interface was removed.
* `Marello\Bundle\WebhookBundle\Model\IntegrationTokenAwareTrait` Trait was removed.

WorkflowBundle
-----
* `Marello\Bundle\WorkflowBundle\Tests\Unit\Async\WorkflowTransitMassProcessorTest::testGetSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\DependencyInjection\MarelloWorkflowExtension::load` [public] Method parameter name changed.
* `Marello\Bundle\WorkflowBundle\DependencyInjection\MarelloWorkflowExtension::load` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction\WorkflowTransitMassActionHandler::handleAsync` [protected] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Async\WorkflowTransitMassProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Async\WorkflowTransitProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Async\Topics` Class was added.
* `Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitMassTopic` Class was removed.
* `Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic` Class was removed.
* `Marello\Bundle\WorkflowBundle\Api\Processor\ProcessWorkflowItem::__construct` [public] Method parameter typing added.
* `Marello\Bundle\WorkflowBundle\Api\Processor\ProcessWorkflowItem::__construct` [public] Method parameter typing removed.
