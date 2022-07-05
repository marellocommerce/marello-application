- [AddressBundle](#addressbundle)
- [BankTransferBundle](#banktransferbundle)
- [CatalogBundle](#catalogbundle)
- [CoreBundle](#corebundle)
- [CustomerBundle](#customerbundle)
- [DataGridBundle](#datagridbundle)
- [DemoDataBundle](#demodatabundle)
- [HealthCheckBundle](#healthcheckbundle)
- [InventoryBundle](#inventorybundle)
- [InvoiceBundle](#invoicebundle)
- [NotificationBundle](#notificationbundle)
- [OrderBundle](#orderbundle)
- [PackingBundle](#packingbundle)
- [PaymentBundle](#paymentbundle)
- [PaymentTermBundle](#paymenttermbundle)
- [PdfBundle](#pdfbundle)
- [PricingBundle](#pricingbundle)
- [ProductBundle](#productbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [RefundBundle](#refundbundle)
- [ReportBundle](#reportbundle)
- [ReturnBundle](#returnbundle)
- [RuleBundle](#rulebundle)
- [SalesBundle](#salesbundle)
- [ShippingBundle](#shippingbundle)
- [SupplierBundle](#supplierbundle)
- [TaxBundle](#taxbundle)
- [UPSBundle](#upsbundle)
- [WorkflowBundle](#workflowbundle)

AddressBundle
-----
* `Marello\Bundle\AddressBundle\Entity\Repository\MarelloAddressRepository::findByAddressParts` [public] Method implementation changed.
* `Marello\Bundle\AddressBundle\Entity\Repository\MarelloAddressRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\AddressBundle\Entity\Repository\MarelloAddressRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\AddressBundle\Entity\Repository\MarelloAddressRepository::findByAddressParts` [public] Method parameter added.

BankTransferBundle
-----

CatalogBundle
-----
* `Marello\Bundle\CatalogBundle\Twig\CategoryExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Twig\CategoryExtension::$doctrine` [private] Property has been removed.
* `Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider::getExcludedCategoriesIds` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Entity\Repository\CategoryRepository::findExcludedCategoriesIds` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Entity\Repository\CategoryRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::createAction` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::update` [protected] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider::$manager` [protected] Property has been removed.
* `Marello\Bundle\CatalogBundle\Entity\Repository\CategoryRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\CatalogBundle\Entity\Repository\CategoryRepository::findExcludedCategoriesIds` [public] Method parameter added.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::createAction` [public] Method parameter added.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::updateAction` [public] Method parameter added.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::update` [protected] Method parameter added.

CoreBundle
-----
* `Marello\Bundle\CoreBundle\Tests\Unit\Twig\CoreExtensionTest::testGetFunctionsAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetter::postFlush` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::process` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Validator\CodeRegexValidator` Class was added.
* `Marello\Bundle\CoreBundle\Validator\Constraints\CodeRegex` Class was added.

CustomerBundle
-----
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::__construct` [public] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::getCompanyChildrenIds` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::getRepository` [protected] Method has been added.
* `Marello\Bundle\CustomerBundle\Tests\Unit\Autocomplete\ParentCompanySearchHandlerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getOrganizations` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Provider\CustomerEmailOwnerProvider::getEmails` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Form\Type\CustomerSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\EventListener\Datagrid\CompanyCustomersSelectGridListener::onResultBeforeQuery` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository::getCompanyIdByCustomerId` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::viewAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::createAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::update` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::viewAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::createAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter name changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method parameter default added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::update` [private] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Autocomplete\CompanyCustomerHandler::getBasicQueryBuilder` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::__construct` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Twig\CustomerExtension::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::createAction` [public] Method parameter added.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::updateAction` [public] Method parameter added.
* `Marello\Bundle\CustomerBundle\Controller\CompanyController::update` [protected] Method parameter added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::createAction` [public] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter removed.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Controller\CustomerController::updateAction` [public] Method parameter typing removed.

DataGridBundle
-----
* `Marello\Bundle\DataGridBundle\Grid\FormatterContextResolver::getResolverPercentageClosure` [public] Method has been added.

DemoDataBundle
-----
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductChannelPricingData::createProductChannelPrice` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductPriceData::createProductPrice` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::createInventoryLevelsForRelatedProducts` [private] Method has been removed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::addProductSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::getWarehouse` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::createWarehouse` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesChannelGroupData::createWarehouseChannelGroupLink` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSupplierData::loadSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::updateCurrentSuppliers` [public] Method has been removed.

HealthCheckBundle
-----
* `Marello\Bundle\HealthCheckBundle\MarelloHealthCheckBundle` Class was added.
* `Marello\Bundle\HealthCheckBundle\Tests\Functional\Controller\IntegrationStatusControllerTest` Class was added.
* `Marello\Bundle\HealthCheckBundle\Provider\Datagrid\LastIntegrationStatusesDataProvider` Class was added.
* `Marello\Bundle\HealthCheckBundle\EventListener\Datagrid\LastIntegrationStatusesGridListener` Class was added.
* `Marello\Bundle\HealthCheckBundle\DependencyInjection\MarelloHealthCheckExtension` Class was added.
* `Marello\Bundle\HealthCheckBundle\Controller\IntegrationStatusController` Class was added.

InventoryBundle
-----
* `Marello\Bundle\InventoryBundle\MarelloInventoryBundle::build` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::getRepository` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::getWarehousesForOrderDataProvider` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\BalancedInventoryLevelUpdateAfterEventListenerTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\BalancedInventoryLevelUpdateAfterEventListenerTest::testRebalanceThresholdHasBeenReachedAndTriggerIsBeingSend` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\BalancedInventoryLevelUpdateAfterEventListenerTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\DependencyInjection\BalancerStrategiesCompilerPassTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Model\InventoryBalancer::setUp` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Entity\Repository\WarehouseRepositoryTest::testGetDefault` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData::load` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseChannelLinkData::load` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Controller\InventoryControllerTest::testUpdateInventoryItemRemoveLevels` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider::getProducts` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider::getBalancedInventoryLevel` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getWarehousesForOrder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::__construct` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::$strategiesRegistry` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator::calculateBatchInventoryLevelQty` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult::getItemsWithQuantity` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::findExistingBalancedInventory` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::up` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryWarehouseTable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryAllocation` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryAllocationItem` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryAllocationForeignKeys` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryAllocationItemForeignKeys` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::setActivityExtension` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::$activityExtension` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\UpdateCurrentWarehouseWithGroup::updateCurrentWarehouse` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\UpdateCurrentWarehouseWithType::updateCurrentWarehouse` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::updateInventory` [private] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::__construct` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::updateBalancedInventory` [private] Method has been added.
* `Marello\Bundle\InventoryBundle\Manager\InventoryItemManager::getInventoryItem` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventory` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventory` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::getWarehouse` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::__construct` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::$inventoryLevelCalculator` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::__construct` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::getResultRecords` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::processEntity` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelType::finishView` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\WarehouseType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\WarehouseType::preSetDataListener` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryBatchSubscriber::handleUnMappedFields` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::submit` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::$doctrine` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryLevelSubscriber::handleUnMappedFields` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::findExistingBalancedInventory` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::$messageProducer` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::$triggerCalculator` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::$repository` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::getWarehouse` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::createWarehouse` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::$messageProducer` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\Datagrid\InventoryLevelGridListener::onBuildBefore` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::getNotifier` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::setNotifier` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::$notifier` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository::findExistingBalancedInventory` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryItemRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository::getInventoryLogRecordsForItem` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository::findExternalLevelsForInventoryItem` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository::__construct` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository::findSystemWarehouseGroup` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::getDefault` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\BalancedInventoryLevelController::recalculateAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\BalancedInventoryLevelController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Controller\InventoryItemController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\InventoryItemController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Controller\InventoryLevelController::chartAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\InventoryLevelController::manageBatchesAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\InventoryLevelController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Controller\WarehouseController::updateDefaultAction` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Controller\WarehouseController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::configure` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::execute` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::processAllProducts` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::processProducts` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::getProducts` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Command\InventoryBalanceCommand::triggerInventoryBalancer` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\WFAStrategiesRegistryTest` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\Calculator\AbstractWHCalculatorTest` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\Quantity\Calculator\SingleWHCalculatorTest` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\DependencyInjection\WFAStrategiesCompilerPassTest` Class was added.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadAllocationData` Class was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry` Class was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy` Class was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\AbstractWHCalculator` Class was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\SingleWHCalculator` Class was added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider` Class was added.
* `Marello\Bundle\InventoryBundle\Provider\WarehouseNotifierChoicesProvider` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendAllocation` Class was added.
* `Marello\Bundle\InventoryBundle\Model\ExtendAllocationItem` Class was added.
* `Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierRegistry` Class was added.
* `Marello\Bundle\InventoryBundle\Model\Allocation\Notifier\WarehouseEmailNotifier` Class was added.
* `Marello\Bundle\InventoryBundle\Model\Allocation\Notifier\WarehouseManualNotifier` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_2\UpdateWarehouseTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_1\UpdateAllocationTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6\AddAllocationAndItemTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadEmailTemplatesData` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryAllocationPriorityChoiceType` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\NotifierChoiceType` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\SendToWarehouseListener` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryBatchEventListener` Class was added.
* `Marello\Bundle\InventoryBundle\Entity\Allocation` Class was added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem` Class was added.
* `Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WFAStrategiesCompilerPass` Class was added.
* `Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WarehouseNotifierRegistryCompilerPass` Class was added.
* `Marello\Bundle\InventoryBundle\Controller\AllocationController` Class was added.
* `Marello\Bundle\InventoryBundle\Command\InventoryReAllocateCronCommand` Class was added.
* `Marello\Bundle\InventoryBundle\Entity\Warehouse::setGroup` [public] Method parameter default added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryBatchCreationEventListener` Class was removed.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Twig\WarehouseExtension::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Model\InventoryBalancer::testMessageSendIsRenderedTemplateAndSubject` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::setEstimation` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getPreferredSupplierWhichCanDropship` [protected] Method has been removed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getWarehousesForOrder` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::$balancedInventoryFactory` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler::$doctrine` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::setContextValidator` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::setEventDispatcher` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::setBalancedInventoryHandler` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::setContextValidator` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::setDoctrineHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::setEventDispatcher` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventory` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::$logRecordRepository` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::$inventoryCalculator` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::$dateHelper` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::$translator` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Form\EventListener\InventoryItemSubscriber::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository::findExistingBalancedInventory` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryItemRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository::getInventoryLogRecordsForItem` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository::findExternalLevelsForInventoryItem` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository::findSystemWarehouseGroup` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::getDefault` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Controller\BalancedInventoryLevelController::recalculateAction` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategyInterface` Interface was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\QtyWHCalculatorInterface` Interface was added.
* `Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface` Interface was added.
* `Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierInterface` Interface was added.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface::getWarehousesForOrder` [public] Method parameter added.

InvoiceBundle
-----
* `Marello\Bundle\InvoiceBundle\Twig\InvoiceExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\CreditmemoPdfRequestHandler::handle` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Pdf\Request\InvoicePdfRequestHandler::handle` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Pdf\Logo\InvoiceLogoPathProvider::fetchImage` [protected] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Migrations\Schema\MarelloInvoiceBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Mapper\AbstractInvoiceMapper::getMapFields` [protected] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Twig\InvoiceExtension::$doctrine` [protected] Property has been removed.

NotificationBundle
-----
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testSendsNotification` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testExceptionIsThrownWhenTemplateIsNotFoundForEntity` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Provider\NotificationActivityListProvider::getTemplate` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Provider\EmailSendProcessor` Class was added.
* `Marello\Bundle\NotificationBundle\Workflow\SendNotificationAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\NotificationBundle\Workflow\SendNotificationAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testMessageSendIsRenderedTemplateAndSubject` [public] Method has been removed.
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testSendsNotificationButDontSaveInDb` [public] Method has been removed.

OrderBundle
-----
* `Marello\Bundle\OrderBundle\Workflow\OrderCancelAction::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::executeAction` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isProductCanDropship` [private] Method parameter added.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isProductCanDropship` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator::isAllRequiredFieldsHasValue` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Validator\AvailableInventoryValidatorTest::testValidateViolationIsBuild` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Validator\AvailableInventoryValidatorTest::getConstraint` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Validator\Constraints\AvailableInventoryContraintTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Twig\OrderExtensionTest::testGetFunctionsAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderAddressFormChangesProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderAddressFormChangesProviderTest::testProcessFormChanges` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderDashboardOrderItemsByStatusProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::load` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderOnDemandWorkflowTest::testWorkflow` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::__construct` [public] Method parameter name changed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::renderForm` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::$twig` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::getTotalRevenueValues` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::getTotalOrdersNumberValues` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::getAverageOrderValues` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderShippingServiceDataProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderShippingServiceDataProvider::getShippingShipFrom` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderStatisticsCurrencyNumberFormatter::formatValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\PossibleShippingMethodsProvider::getPossibleShippingMethods` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::getTopProductsByRevenue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider::getItems` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadEmailTemplatesData::findExistingTemplate` [protected] Method has been added.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadEmailTemplatesData::getVersion` [public] Method has been added.
* `Marello\Bundle\OrderBundle\Form\Type\OrderItemType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::$salesChannelRepository` [private] Property has been removed.
* `Marello\Bundle\OrderBundle\Factory\OrderPaymentContextFactory::create` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method parameter name changed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::create` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::getShippingOrigin` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::$estimation` [private] Property has been removed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::$orderWarehousesProvider` [private] Property has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Workflow\PurchaseOrderWorkflowCompletedListener::onPurchaseOrderCompleted` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Workflow\PurchaseOrderWorkflowCompletedListener::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderInventoryAllocationListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderInventoryAllocationListener::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemProductUnitListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::onOrderPaid` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::onOrderShipped` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::getBalancedInventoryLevel` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter name changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter name changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter name changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getOrdersCurrencies` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\OrderBundle\DependencyInjection\Compiler\EmailTwigSandboxConfigurationPass::getTags` [protected] Method has been added.
* `Marello\Bundle\OrderBundle\Controller\OrderAjaxController::formChangesAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderAjaxController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\OrderBundle\Controller\OrderController::update` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderController::updateAddressAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\OrderBundle\Controller\OrderDashboardController::orderitemsByStatusAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventoryConstraint` Class was added.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderCompanyCustomerFormChangesProviderTest` Class was added.
* `Marello\Bundle\OrderBundle\Provider\OrderCompanyCustomerFormChangesProvider` Class was added.
* `Marello\Bundle\OrderBundle\EventListener\OrderViewListener` Class was added.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method parameter default added.
* `Marello\Bundle\OrderBundle\Workflow\OrderPickAndPackAction` Class was removed.
* `Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventory` Class was removed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::__construct` [public] Method parameter removed.
* `Marello\Bundle\OrderBundle\Workflow\OrderShipAction::$warehousesProvider` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider::$templatingEngine` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardOrderItemsByStatusProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardOrderItemsByStatusProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::$orderRepository` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider::$dateHelper` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderShippingServiceDataProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Provider\OrderShippingServiceDataProvider::$entityManager` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::$orderRepository` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::$orderItemRepository` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDashboardStatisticProvider::$productRepository` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::__construct` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::setEstimation` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::isEstimation` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method parameter removed.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::__construct` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::$availableInventoryProvider` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemStatusListener::$eventDispatcher` [protected] Property has been removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter default removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter default removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter typing added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter typing removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter default removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getOrdersCurrencies` [public] Method parameter added.

PackingBundle
-----
* `Marello\Bundle\PackingBundle\Tests\Unit\Mapper\OrderToPackingSlipMapperTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\Mapper\OrderToPackingSlipMapperTest::testMap` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\EventListener\CreatePackingSlipEventListenerTest::testOnCreatePackingSlip` [public] Method parameter name changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\EventListener\CreatePackingSlipEventListenerTest::testOnCreatePackingSlip` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\EventListener\CreatePackingSlipEventListenerTest::onCreatePackingSlipDataProvider` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\EventListener\CreatePackingSlipEventListenerTest::prepareEvent` [protected] Method parameter name changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\EventListener\CreatePackingSlipEventListenerTest::prepareEvent` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Functional\DataFixtures\LoadPackingSlipData::getDependencies` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Functional\DataFixtures\LoadPackingSlipData::load` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Functional\Controller\PackingSlipControllerTest::testViewAction` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::createMarelloPackingSlipTable` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::createMarelloPackingSlipItemTable` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::addMarelloPackingSlipForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\AbstractPackingSlipMapper::getMapFields` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::map` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::getItems` [protected] Method parameter name changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::getItems` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method parameter name changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\EventListener\CreatePackingSlipEventListener::onCreatePackingSlip` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\EventListener\CreatePackingSlipEventListener::isCorrectContext` [protected] Method has been added.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::getSourceEntity` [public] Method has been added.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::setSourceEntity` [public] Method has been added.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::$sourceEntity` [protected] Property has been added.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4\UpdatePackingSlipTable` Class was added.
* `Marello\Bundle\PackingBundle\Tests\Unit\Mapper\OrderToPackingSlipMapperTest::$warehousesProvider` [protected] Property has been removed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::__construct` [public] Method has been removed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::getItems` [protected] Method parameter removed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::getItems` [protected] Method parameter typing added.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::getItems` [protected] Method parameter typing removed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method parameter typing removed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::$warehousesProvider` [protected] Property has been removed.
* `Marello\Bundle\PackingBundle\EventListener\CreatePackingSlipEventListener::isCorrectOrderContext` [protected] Method has been removed.

PaymentBundle
-----
* `Marello\Bundle\PaymentBundle\Twig\PaymentMethodExtension::getPaymentMethodConfigRenderData` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProviderTest::$aclHelper` [private] Property has been added.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProviderTest::$aclHelper` [private] Property has been added.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Handler\RulesPaymentMethodDisableHandlerDecoratorTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Handler\RulesPaymentMethodDisableHandlerDecoratorTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Event\BasicMethodRemovalEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Event\BasicMethodRenamingEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Condition\PaymentMethodHasPaymentRulesTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Unit\Condition\PaymentMethodHasPaymentRulesTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetByDestinationAndCurrency` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetByCurrencyWithoutDestination` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetEnabledRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetByCurrency` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::testGetByCurrencyWhenCurrencyNotExists` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\BasicPaymentMethodsViewsProvider::getApplicableMethodsViews` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::getPaymentMethodsConfigsRules` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::$filtrationService` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::$repository` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::getPaymentMethodsConfigsRules` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::$filtrationService` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::$repository` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::renderForm` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::$twig` [protected] Property has been added.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::handleMethodDisable` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::$handler` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::$repository` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::$paymentMethodProvider` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Method\Event\BasicMethodRemovalEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Event\BasicMethodRenamingEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByDestinationAndCurrency` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByCurrency` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByCurrencyWithoutDestination` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getConfigsWithEnabledRuleAndMethod` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getEnabledRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass::getTags` [protected] Method has been added.
* `Marello\Bundle\PaymentBundle\Controller\PaymentAjaxController::formChangesAction` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Controller\PaymentAjaxController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PaymentBundle\Controller\PaymentController::update` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Controller\PaymentController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PaymentBundle\Controller\PaymentMethodsConfigsRuleController::update` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Controller\PaymentMethodsConfigsRuleController::markMassAction` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Controller\PaymentMethodsConfigsRuleController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::getRulesByMethod` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::$repository` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::__construct` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::getRulesByMethod` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::$repository` [private] Property has been removed.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PaymentBundle\Provider\FormChanges\AvailablePaymentMethodsFormChangesProvider::$templatingEngine` [protected] Property has been removed.
* `Marello\Bundle\PaymentBundle\Method\Handler\RulesPaymentMethodDisableHandlerDecorator::__construct` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByDestinationAndCurrency` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByCurrency` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getByCurrencyWithoutDestination` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getConfigsWithEnabledRuleAndMethod` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getRulesByMethod` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::getEnabledRulesByMethod` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::__construct` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasEnabledPaymentRules::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::__construct` [public] Method parameter added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules::__construct` [public] Method parameter typing removed.

PaymentTermBundle
-----
* `Marello\Bundle\PaymentTermBundle\Tests\Functional\Controller\PaymentTermControllerTest::assertPaymentTermSave` [protected] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Tests\Functional\Controller\SystemConfigurationControllerTest::testSystemConfigurationUpdate` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::createAction` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::update` [protected] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::createAction` [public] Method parameter added.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::updateAction` [public] Method parameter added.
* `Marello\Bundle\PaymentTermBundle\Controller\PaymentTermController::update` [protected] Method parameter added.

PdfBundle
-----
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::__construct` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::initialize` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::executeAction` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::buildFileAttachment` [protected] Method implementation changed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::normalizeToOption` [protected] Method implementation changed.
* `Marello\Bundle\PdfBundle\Tests\Unit\Workflow\Action\SendEmailTemplateAttachmentActionTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Tests\Unit\DependencyInjection\CompilerPass\DocumentTableProviderPassTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Tests\Unit\DependencyInjection\CompilerPass\RenderParameterProviderPassTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Controller\DownloadController::downloadAction` [public] Method implementation changed.
* `Marello\Bundle\PdfBundle\Controller\DownloadController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::getMimeTypeGuesser` [protected] Method has been removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::__construct` [public] Method parameter added.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::$mimeTypeGuesser` [protected] Property has been removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::$renderer` [protected] Property has been removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::$registry` [protected] Property has been removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::$validator` [protected] Property has been removed.
* `Marello\Bundle\PdfBundle\Workflow\Action\SendEmailTemplateAttachmentAction::$emailOriginHelper` [protected] Property has been removed.

PricingBundle
-----
* `Marello\Bundle\PricingBundle\Tests\Unit\Twig\PricingExtensionTest::testGetFunctions` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Unit\Provider\ChannelPriceProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Unit\Provider\ChannelPriceProviderTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\PricingBundle\Tests\Unit\DependencyInjection\Compiler\SubtotalProviderPassTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testCreateNewPriceListWithPrices` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledPriceListJsonApiTest::testCreateNewPriceListWithDefaultPrice` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::processFormChanges` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\EventListener\Datagrid\ChannelPricesDatagridListener::onBuildBefore` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\EventListener\Datagrid\PricesDatagridListener::onBuildBefore` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Controller\PricingController::getCurrencyByChannelAction` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Controller\PricingController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider::$registry` [protected] Property has been removed.

ProductBundle
-----
* `Marello\Bundle\ProductBundle\MarelloProductBundle::build` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecorator::getRelationField` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Validator\ProductSupplierRelationsDropshipValidator::getWarehouse` [private] Method implementation changed.
* `Marello\Bundle\ProductBundle\Twig\ProductExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Twig\ProductExtension::getProductBySku` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Twig\ProductExtensionTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Twig\ProductExtensionTest::testGetFunctions` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Twig\ProductExtensionTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\ProductBundle\Tests\Unit\Twig\ProductUnitExtensionTest::testGetFunctionsAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Twig\ProductUnitExtensionTest::testGetFiltersAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Provider\ProductTaxCodeProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Provider\ProductTaxCodeProviderTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Entity\Repository\ProductRepositoryTest::testPurchaseOrderItemCandidates` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testCreateNewProduct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testUpdateProduct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Provider\OrderItemProductUnitProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Provider\OrderItemProductUnitProvider::processFormChanges` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::processFormChanges` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductSupplierSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\AttributeFormViewListener::onEdit` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\AttributeFormViewListener::onViewList` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Doctrine\ProductDropshipEventListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Datagrid\ProductGridListener::onBuildBefore` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByChannel` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getProductIdsBySalesChannelIds` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findBySalesChannel` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method parameter name changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByDataKey` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Duplicator\ProductDuplicator::duplicate` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::defineBaseSku` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\DependencyInjection\Compiler\EmailTwigSandboxConfigurationPass::getTags` [protected] Method has been added.
* `Marello\Bundle\ProductBundle\Controller\ProductController::createStepOne` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::update` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::assignSalesChannelsAction` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Controller\VariantController::createVariantAction` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\VariantController::updateVariantAction` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\VariantController::updateVariant` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\VariantController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::__construct` [public] Method parameter name changed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::sendMail` [private] Method implementation changed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$logger` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$entityManager` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$datagridManager` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$tokenStorage` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$emailModelFactory` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::$emailProcessor` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadAllocationStatusData` Class was added.
* `Marello\Bundle\ProductBundle\Twig\ProductExtension::__construct` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Twig\ProductExtension::$channelProvider` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Twig\ProductExtension::$categoriesIdsProvider` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Provider\OrderItemProductUnitProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Provider\OrderItemProductUnitProvider::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider::$registry` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByChannel` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getProductIdsBySalesChannelIds` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findBySalesChannel` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method parameter typing removed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findOneBySku` [public] Method parameter default removed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByDataKey` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByDataKey` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::__construct` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Duplicator\SkuIncrementor::$productClass` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::assignSalesChannelsAction` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Controller\VariantController::createVariantAction` [public] Method parameter added.
* `Marello\Bundle\ProductBundle\Controller\VariantController::updateVariant` [protected] Method parameter added.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::__construct` [public] Method parameter removed.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor::__construct` [public] Method parameter typing removed.

PurchaseOrderBundle
-----
* `Marello\Bundle\PurchaseOrderBundle\Workflow\Action\ReceivePurchaseOrderAction::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::testPostFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::testAdviceCommandWillSendNotification` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Controller\PurchaseOrderControllerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\MarelloPurchaseOrderBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\MarelloPurchaseOrderBundleInstaller::createMarelloPurchaseOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::getPurchasePrice` [private] Method parameter added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::getPurchasePrice` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::getLinkedWarehouse` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::$availableInventoryProvider` [private] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::$doctrine` [private] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::isActive` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::execute` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::__construct` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::createStepOne` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::update` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::productsBySupplierAction` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_4\MarelloPurchaseOrderBundle` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWarehouseListener::__construct` [public] Method parameter added.

RefundBundle
-----
* `Marello\Bundle\RefundBundle\Tests\Unit\Twig\RefundExtensionTest::testGetFunctionsAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Schema\MarelloRefundBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Schema\MarelloRefundBundleInstaller::up` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Form\Type\OrderItemRefundType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Controller\RefundAjaxController::formChangesAction` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Controller\RefundAjaxController::formCreateAction` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Controller\RefundAjaxController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\RefundBundle\Controller\RefundController::update` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Controller\RefundController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\RefundBundle\Migrations\Schema\v1_4_1\MarelloRefundBundle` Class was added.

ReportBundle
-----
* `Marello\Bundle\ReportBundle\Controller\ReportController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\ReportBundle\Controller\ReportController::getSubscribedServices` [public] Method has been added.

ReturnBundle
-----
* `Marello\Bundle\ReturnBundle\Workflow\InspectionAction::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Tests\Unit\Twig\ReturnExtensionTest::testGetFunctionsAreRegisteredInExtension` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Tests\Functional\Controller\ReturnControllerTest::testDatagrids` [public] Method has been added.
* `Marello\Bundle\ReturnBundle\Provider\ReturnShippingServiceDataProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Provider\ReturnShippingServiceDataProvider::getShippingShipTo` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Entity\Repository\ReturnItemRepository::getReturnQuantityByReason` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::createAction` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\ReturnBundle\Provider\ReturnShippingServiceDataProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\ReturnBundle\Provider\ReturnShippingServiceDataProvider::$entityManager` [protected] Property has been removed.

RuleBundle
-----
* `Marello\Bundle\RuleBundle\Datagrid\Extension\MassAction\StatusMassActionHandler::getResponse` [protected] Method implementation changed.

SalesBundle
-----
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::__construct` [public] Method parameter name changed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::checkActiveChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::getChannelNameByCode` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::getRepository` [protected] Method has been added.
* `Marello\Bundle\SalesBundle\Tests\Unit\Twig\SalesExtensionTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\Twig\SalesExtensionTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\SalesBundle\Tests\Unit\Form\Handler\SalesChannelGroupHandlerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\Form\Handler\SalesChannelGroupHandlerTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::$aclHelper` [private] Property has been added.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelListenerTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\SalesBundle\Tests\Functional\Entity\Repository\SalesChannelRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Entity\Repository\SalesChannelRepositoryTest::testGetActiveChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Entity\Repository\SalesChannelRepositoryTest::testGetDefaultActiveChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData::loadSalesChannels` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::testUpdate` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::testView` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertSalesChannelSave` [protected] Method parameter name changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertSalesChannelSave` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertViewPage` [protected] Method parameter name changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertViewPage` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelGroupControllerTest::assertSalesChannelGroupSave` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Provider\ChannelProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Provider\ChannelProvider::getExcludedSalesChannelsIds` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Data\ORM\AssignDefaultGroupForSalesChannels::load` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelType::preSetDataListener` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelTypeSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler::getSystemSalesChannelsGroup` [private] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber::getDefaultChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::rebalanceForSalesChannelGroup` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::$messageProducer` [private] Property has been removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::getSystemWarehouseChannelGroupLink` [private] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository::findSystemChannelGroup` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::findOneBySalesChannel` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::findExcludedSalesChannelIds` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::getActiveChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::getDefaultActiveChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelTypeRepository::search` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelTypeRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\SalesBundle\Controller\ConfigController::salesChannelAction` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\ConfigController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelController::update` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelGroupController::update` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelGroupController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelTypeController::searchAction` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelTypeController::createAction` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Controller\SalesChannelTypeController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Tests\Functional\Provider\ChannelProviderTest` Class was added.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertSalesChannelSave` [protected] Method parameter default added.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::assertViewPage` [protected] Method parameter default added.
* `Marello\Bundle\SalesBundle\Tests\Provider\ChannelProviderTest` Class was removed.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::__construct` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Twig\SalesExtension::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::getOperationExecuteParams` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelGroupControllerTest::getOperationExecuteParams` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Provider\ChannelProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Provider\ChannelProvider::$manager` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Form\Handler\SalesChannelGroupHandler::$manager` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber::$em` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::$installed` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::$session` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::__construct` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelListener::$installed` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelGroupRepository::findSystemChannelGroup` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::findOneBySalesChannel` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::findExcludedSalesChannelIds` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::getActiveChannels` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::getDefaultActiveChannels` [public] Method parameter added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelTypeRepository::setAclHelper` [public] Method has been removed.

ShippingBundle
-----
* `Marello\Bundle\ShippingBundle\Twig\ShippingMethodExtension::getShippingMethodConfigRenderData` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\ShippingPriceProviderTest::testGetApplicableMethodsViews` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProviderTest::$aclHelper` [private] Property has been added.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProviderTest::$aclHelper` [private] Property has been added.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Handler\RulesShippingMethodDisableHandlerDecoratorTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Handler\RulesShippingMethodDisableHandlerDecoratorTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Event\BasicMethodRemovalEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Event\BasicMethodRenamingEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Event\BasicMethodTypeRemovalEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Condition\ShippingMethodHasShippingRulesTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Condition\ShippingMethodHasShippingRulesTest::$aclHelper` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetByDestinationAndCurrency` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetByCurrencyWithoutDestination` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetEnabledRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetByCurrency` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::testGetByCurrencyWhenCurrencyNotExists` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\Price\ShippingPriceProvider::getApplicableMethodsViews` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::getShippingMethodsConfigsRules` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::$filtrationService` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::$repository` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::getShippingMethodsConfigsRules` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::$filtrationService` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::$repository` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::handleMethodDisable` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::$handler` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::$repository` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::$shippingMethodProvider` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Method\Event\BasicMethodRemovalEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Event\BasicMethodRenamingEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Event\BasicMethodTypeRemovalEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByDestinationAndCurrency` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByCurrency` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByCurrencyWithoutDestination` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getConfigsWithEnabledRuleAndMethod` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getEnabledRulesByMethod` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::$aclHelper` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::process` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Controller\ShippingMethodsConfigsRuleController::update` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Controller\ShippingMethodsConfigsRuleController::markMassAction` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Controller\ShippingMethodsConfigsRuleController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::getRulesByMethod` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::$repository` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::__construct` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::getRulesByMethod` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::$repository` [private] Property has been removed.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination\RegardlessDestinationMethodsConfigsRulesByContextProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic\BasicMethodsConfigsRulesByContextProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Method\Handler\RulesShippingMethodDisableHandlerDecorator::__construct` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::setAclHelper` [public] Method has been removed.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByDestinationAndCurrency` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByCurrency` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getByCurrencyWithoutDestination` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getConfigsWithEnabledRuleAndMethod` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getRulesByMethod` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::getEnabledRulesByMethod` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::__construct` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasEnabledShippingRules::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::__construct` [public] Method parameter added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::__construct` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules::__construct` [public] Method parameter typing removed.

SupplierBundle
-----
* `Marello\Bundle\SupplierBundle\Tests\Unit\Twig\SupplierExtensionTest::testGetFunctions` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Tests\Functional\Entity\SupplierOwnerIsSetTest::testCreateNewSupplier` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData::loadSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Tests\Functional\Controller\SupplierControllerTest::testCreateNewSupplier` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\MarelloSupplierBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\MarelloSupplierBundleInstaller::createMarelloSupplierSupplierTable` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSuppliersWithWarehouseAndInvLevels::getWarehouse` [private] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSuppliersWithWarehouseAndInvLevels::createWarehouse` [private] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierType::preSetDataListener` [public] Method has been added.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preUpdate` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\EventListener\Doctrine\SupplierDropshipEventListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Entity\Supplier::getCode` [public] Method has been added.
* `Marello\Bundle\SupplierBundle\Entity\Supplier::setCode` [public] Method has been added.
* `Marello\Bundle\SupplierBundle\Entity\Supplier::$code` [protected] Property has been added.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::createAction` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::update` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::updateAddressAction` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::getSupplierDefaultDataAction` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\v1_5_1\MarelloSupplierBundle` Class was added.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSuppliersWithCode` Class was added.
* `Marello\Bundle\SupplierBundle\Form\Handler\SupplierHandler` Class was removed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::createAction` [public] Method parameter added.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::updateAction` [public] Method parameter added.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::update` [protected] Method parameter added.

TaxBundle
-----
* `Marello\Bundle\TaxBundle\Tests\Unit\EventListener\TaxEventDispatcherTest::testDispatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\DependencyInjection\Compiler\ResolverEventConnectorPassTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\DependencyInjection\CompilerPass\TaxMapperPassTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\Controller\TaxJurisdictionControllerTest::assertTaxJurisdictionSave` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Form\Type\TaxCodeSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Event\TaxEventDispatcher::dispatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::createAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Controller\TaxJurisdictionController::update` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxJurisdictionController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::createAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::createAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::updateAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::createAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::updateAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::update` [protected] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::createAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::updateAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::update` [protected] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::createAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::updateAction` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method parameter added.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::update` [protected] Method parameter default removed.

UPSBundle
-----
* `Marello\Bundle\UPSBundle\Validator\Constraints\RemoveUsedShippingServiceValidator::handleValidationResult` [private] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\Validator\Constraints\RemoveUsedShippingServiceValidatorTest::testValidateWithErrors` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Unit\DependencyInjection\MarelloUPSExtensionTest::testLoad` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Method\Factory\UPSShippingMethodFactory::createTypes` [private] Method implementation changed.
* `Marello\Bundle\UPSBundle\Controller\AjaxUPSController::getShippingServicesByCountryAction` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Controller\AjaxUPSController::validateConnectionAction` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Controller\AjaxUPSController::getErrorMessageByValidatorResult` [private] Method implementation changed.
* `Marello\Bundle\UPSBundle\Controller\AjaxUPSController::getSubscribedServices` [public] Method has been added.

WorkflowBundle
-----
* `Marello\Bundle\WorkflowBundle\Tests\Unit\Async\WorkflowTransitMassProcessorTest::testGetSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Manager\WorkflowTransitMassManager::sendEmailReport` [protected] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Controller\WorkflowController::massActionLogAction` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Controller\WorkflowController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\WorkflowBundle\Async\WorkflowTransitMassProcessor::getSubscribedTopics` [public] Method implementation changed.
* `Marello\Bundle\WorkflowBundle\Async\Topics` Class was added.
* `Marello\Bundle\WorkflowBundle\Async\WorkflowTransitProcessor` Class was added.

