## 6.0.0 (2024-11-05)

- [AddressBundle](#addressbundle)
- [BankTransferBundle](#banktransferbundle)
- [CatalogBundle](#catalogbundle)
- [CoreBundle](#corebundle)
- [CustomerBundle](#customerbundle)
- [DataGridBundle](#datagridbundle)
- [DemoDataBundle](#demodatabundle)
- [HealthCheckBundle](#healthcheckbundle)
- [ImportExportBundle](#importexportbundle)
- [InventoryBundle](#inventorybundle)
- [InvoiceBundle](#invoicebundle)
- [LocaleBundle](#localebundle)
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
- [ReportBundle](#reportbundle)
- [ReturnBundle](#returnbundle)
- [RuleBundle](#rulebundle)
- [SalesBundle](#salesbundle)
- [ShipmentBundle](#shipmentbundle)
- [ShippingBundle](#shippingbundle)
- [SupplierBundle](#supplierbundle)
- [TaxBundle](#taxbundle)
- [UPSBundle](#upsbundle)
- [WebhookBundle](#webhookbundle)
- [WorkflowBundle](#workflowbundle)

AddressBundle
-----
* `Marello\Bundle\AddressBundle\Tests\Functional\Api\DataFixtures\LoadAddressData::createMarelloAddress` [protected] Method implementation changed.

BankTransferBundle
-----
* `Marello\Bundle\BankTransferBundle\MarelloBankTransferBundle::getContainerExtension` [public] Method has been removed.

CatalogBundle
-----
* `Marello\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData::load` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Migrations\Schema\MarelloCatalogBundleInstaller::createCatalogCategoryTable` [protected] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Entity\Category::prePersist` [public] Method has been added.
* `Marello\Bundle\CatalogBundle\Entity\Category::preUpdate` [public] Method has been added.
* `Marello\Bundle\CatalogBundle\Controller\CategoryController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\CatalogBundle\Entity\Category::setDescription` [public] Method parameter default added.
* `Marello\Bundle\CatalogBundle\Entity\Category::setName` [public] Method parameter typing added.
* `Marello\Bundle\CatalogBundle\Entity\Category::setCode` [public] Method parameter typing added.
* `Marello\Bundle\CatalogBundle\Entity\Category::setDescription` [public] Method parameter typing added.

CoreBundle
-----
* `Marello\Bundle\CoreBundle\Twig\FileExtension::getFileById` [public] Method implementation changed.
* `Marello\Bundle\CoreBundle\Model\HashAwareInterface` Interface was added.
* `Marello\Bundle\CoreBundle\Model\HashAwareTrait` Trait was added.

CustomerBundle
-----
* `Marello\Bundle\CustomerBundle\Tests\Unit\Form\Type\CompanySelectTypeTest::testConfigureOptions` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Unit\Form\Type\ParentCompanySelectTypeTest::testConfigureOptions` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCompanyData::load` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerData::createCustomer` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testGetCustomerById` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testCreateNewCustomer` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testCreateNewCustomerWithoutAddress` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testUpdateAddressExistingCustomer` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testCreateDuplicateCustomer` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller::createMarelloCompanyTable` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\MarelloCustomerBundleInstaller::createMarelloCustomerTable` [protected] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Form\Type\CustomerType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Entity\Company::prePersist` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Company::preUpdate` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setPrimaryAddress` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setShippingAddress` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Entity\Customer::prePersist` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::preUpdate` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::getCustomerNumber` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setCustomerNumber` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::isHidden` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setIsHidden` [public] Method has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::$customerNumber` [protected] Property has been added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::$isHidden` [protected] Property has been added.
* `Marello\Bundle\CustomerBundle\DependencyInjection\MarelloCustomerExtension::load` [public] Method implementation changed.
* `Marello\Bundle\CustomerBundle\Validator\UniqueCustomerEmailValidator` Class was added.
* `Marello\Bundle\CustomerBundle\Validator\Constraints\UniqueCustomerEmail` Class was added.
* `Marello\Bundle\CustomerBundle\Provider\CustomerMetricsProvider` Class was added.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5_4\MarelloCustomerBundle` Class was added.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5_3\MarelloCustomerBundle` Class was added.
* `Marello\Bundle\CustomerBundle\Migrations\Schema\v1_5_2\MarelloCustomerBundle` Class was added.
* `Marello\Bundle\CustomerBundle\Migrations\Data\ORM\MergeDuplicateCustomerData` Class was added.
* `Marello\Bundle\CustomerBundle\EventListener\Entity\CustomerEventListener` Class was added.
* `Marello\Bundle\CustomerBundle\Entity\Repository\CustomerRepository` Class was added.
* `Marello\Bundle\CustomerBundle\Api\Processor\SetLowerCaseEmailId` Class was added.
* `Marello\Bundle\CustomerBundle\Entity\Company::setTaxIdentificationNumber` [public] Method parameter default added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setPrimaryAddress` [public] Method parameter default added.
* `Marello\Bundle\CustomerBundle\Entity\Customer::setShippingAddress` [public] Method parameter default added.
* `Marello\Bundle\CustomerBundle\MarelloCustomerBundle::build` [public] Method has been removed.
* `Marello\Bundle\CustomerBundle\MarelloCustomerBundle::getContainerExtension` [public] Method has been removed.
* `Marello\Bundle\CustomerBundle\Tests\Functional\Api\CustomerJsonApiTest::testUpdateEmailExistingCustomer` [public] Method has been removed.
* `Marello\Bundle\CustomerBundle\Entity\Company::setName` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Entity\Company::setTaxIdentificationNumber` [public] Method parameter typing added.
* `Marello\Bundle\CustomerBundle\Entity\EmailAddressTrait` Trait was added.
* `Marello\Bundle\CustomerBundle\Entity\FullNameTrait` Trait was added.
* `Marello\Bundle\CustomerBundle\Entity\HasEmailAddressTrait` Trait was removed.
* `Marello\Bundle\CustomerBundle\Entity\HasFullNameTrait` Trait was removed.

DataGridBundle
-----
* `Marello\Bundle\DataGridBundle\Grid\FormatterContextResolver::getResolverPercentageClosure` [public] Method implementation changed.
* `Marello\Bundle\DataGridBundle\Extension\Totals\OrmTotalsExtension` Class was removed.

DemoDataBundle
-----
* `Marello\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadDashboardData::getWidgets` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadCategoryData::getQueryBuilder` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadCustomerData::createCustomer` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData::createOrder` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData::createOrderItem` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPaymentRule::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPaymentRule::getOrganization` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPaymentRuleConfig::addMethodConfigToDefaultPaymentRule` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPaymentRuleConfig::getOrganization` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData::createProduct` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductImageData::loadProductImages` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductInventoryData::createProductInventory` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSalesChannelTaxCodeData::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductSupplierData::addProductSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductVariantData::loadProductVariant` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductVariantData::createVariant` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData::load` [public] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesChannelGroupData::loadSalesChannelGroups` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData::loadSalesChannels` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadShippingRule::getOrganization` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadShippingRuleConfig::addMethodConfigToDefaultShippingRule` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadShippingRuleConfig::getOrganization` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSupplierData::loadSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadTaxJurisdictionData::getCountryByIso2Code` [private] Method implementation changed.
* `Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadTaxJurisdictionData::getRegionByCountryAndCode` [private] Method implementation changed.

HealthCheckBundle
-----

ImportExportBundle
-----
* `Marello\Bundle\ImportExportBundle\DependencyInjection\MarelloImportExportExtension` Class was added.
* `Marello\Bundle\ImportExportBundle\Controller\ImportExportController` Class was added.
* `Marello\Bundle\ImportExportBundle\MarelloImportExportBundle::getParent` [public] Method has been removed.

InventoryBundle
-----
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest::testGetWarehouseResults` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest::$doctrineHelper` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\Calculator\AbstractWHCalculatorTest::mockProduct` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::getWarehousesForOrderDataProvider` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::$doctrineHelper` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\InventoryUpdateEventListenerTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\InventoryUpdateEventListenerTest::testHandleUpdateInventoryEvent` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\InventoryUpdateEventListenerTest::testHandleEventWithVirtualInventoryContext` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\InventoryUpdateEventListenerTest::$webhookProducer` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Entity\Repository\WarehouseRepositoryTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData::load` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData::createProductInventory` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData::handleInventoryUpdate` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadWarehouseChannelLinkData::load` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Functional\Controller\InventoryControllerTest::testUpdateInventoryItemAddLevelAndIncrease` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getWarehouseResults` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getLinkedWarehouses` [private] Method parameter added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getLinkedWarehouses` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::setAllocationItemFilterProvider` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$linkedWarehouses` [private] Property has been removed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$allocationItemFilterProvider` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\SingleWHCalculator::calculate` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::createAllocationItems` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getWarehousesForOrder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::setOrderItemsForAllocation` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::$items` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryWarehouseTable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryAllocationItem` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryAllocationForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_10\MarelloInventoryBundle::up` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_10\MarelloInventoryBundle::updateWarehouseTable` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_10\MarelloInventoryBundle::updateInventoryAllocationTable` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_10\MarelloInventoryBundle::updateInventoryAllocationItemTable` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\CreateDefaultWarehouseChannelGroupLink::createDefaultWarehouseChannelGroupLink` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadEmailTemplatesData::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadEmailTemplatesData::getVersion` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData::loadDefaultWarehouse` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData::createAddress` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseGroupData::loadWarehouseGroups` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\UpdateCurrentInventoryItemsWithOrganization::updateCurrentInventoryItems` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Data\ORM\UpdateCurrentInventoryLevelsWithOrganization::updateCurrentInventoryLevels` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Logging\ChartBuilder::getChartData` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::processEntity` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::createNewEntityContext` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::createSerializedEntityKey` [private] Method parameter name changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::createSerializedEntityKey` [private] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::createSerializedEntityKey` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::addDuplicateValidationError` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::addProductNotExistValidationError` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::setTokenAccessor` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::addInventoryItemNotExistValidationError` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::$tokenAccessor` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\ImportExport\DataConverter\InventoryLevelImportDataConverter::getHeaderConversionRules` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\ImportExport\DataConverter\InventoryLevelImportDataConverter::getBackendHeader` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelType::finishView` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Form\Type\WarehouseType::preSetDataListener` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::createInventoryLevelForRelatedProduct` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryLevelUpdateAfterEventListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryLevelUpdateAfterEventListener::createLogRecord` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupLinkRebalanceListener::onFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::getInventoryBatches` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::setInventoryBatches` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::$inventoryBatches` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::setInventoryQty` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::getInventoryQty` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::getVirtualInventoryQty` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::$inventoryQty` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\EventListener\InventoryLevelRebalanceEventListenerTest` Class was added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\MultiWHCalculator` Class was added.
* `Marello\Bundle\InventoryBundle\Provider\Allocation\AllocationItemFilterProvider` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_11\MarelloInventoryBundle` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Doctrine\InventoryLevelRebalanceEventListener` Class was added.
* `Marello\Bundle\InventoryBundle\Api\Processor\CustomizeInventoryLevelFormData` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryItemEventListener` Class was removed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\QuantityWFAStrategyTest::$warehouseChannelGroupLinkRepository` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter typing removed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getWarehouseResults` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::createNewEntityContext` [protected] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::getProduct` [protected] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::getInventoryItem` [protected] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy::getWarehouse` [protected] Method parameter typing added.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\EventListener\ExternalWarehouseEventListener::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\InventoryLevelUpdateAfterEventListener::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Entity\InventoryLevel::$inventory` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Provider\Allocation\AllocationItemFilterInterface` Interface was added.

InvoiceBundle
-----
* `Marello\Bundle\InvoiceBundle\Migrations\Data\ORM\UpdateEmailTemplates::findExistingTemplate` [protected] Method implementation changed.

LocaleBundle
-----
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::__construct` [public] Method implementation changed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::getLocalizedModel` [public] Method implementation changed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::setEntityLocalizationProvider` [public] Method has been removed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::setEmailTemplateContentProvider` [public] Method has been removed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::__construct` [public] Method parameter added.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::$configManager` [protected] Property has been removed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::$entityLocalizationProvider` [protected] Property has been removed.
* `Marello\Bundle\LocaleBundle\Manager\EmailTemplateManager::$emailTemplateContentProvider` [protected] Property has been removed.

ManualShippingBundle
-----
* `Marello\Bundle\ManualShippingBundle\Tests\Functional\DataFixtures\LoadManualShippingIntegration::getOrganization` [private] Method implementation changed.
* `Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration::getOrganization` [private] Method implementation changed.

NotificationBundle
-----
* `Marello\Bundle\NotificationBundle\Tests\Functional\Email\SendProcessorTest::testExceptionIsThrownWhenTemplateIsNotFoundForEntity` [public] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Migrations\Schema\MarelloNotificationBundleInstaller::createMarelloNotificationTable` [protected] Method implementation changed.
* `Marello\Bundle\NotificationBundle\Email\SendProcessor` Class was removed.

NotificationMessageBundle
-----
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::testOnCreateNew` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::testOnCreateOld` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::$registry` [private] Property has been removed.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::$configManager` [private] Property has been removed.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::$doctrineHelper` [private] Property has been added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::$messageProducer` [private] Property has been added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest::$notificationFactory` [private] Property has been added.
* `Marello\Bundle\NotificationMessageBundle\Migrations\Schema\MarelloNotificationMessageBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Migrations\Schema\MarelloNotificationMessageBundleInstaller::createMarelloNotificationMessageTable` [protected] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Controller\AjaxNotificationMessageController::resolveAction` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Controller\AjaxNotificationMessageController::getSubscribedServices` [public] Method has been added.
* `Marello\Bundle\NotificationMessageBundle\Controller\NotificationMessageController::notificationMessagesWidgetAction` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Controller\NotificationMessageController::getSubscribedServices` [public] Method implementation changed.
* `Marello\Bundle\NotificationMessageBundle\Migrations\Schema\v1_1\MarelloNotificationMessageBundle` Class was added.

OrderBundle
-----
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\DiscountSubtotalProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\DiscountSubtotalProviderTest::testGetName` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\DiscountSubtotalProviderTest::testGetSubtotal` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderDashboardOrderItemsByStatusProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderDashboardOrderItemsByStatusProviderTest::testgetOrderItemsGroupedByStatusDQL` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderDashboardOrderItemsByStatusProviderTest::getOrderItemsGroupedByStatusDQLDataProvider` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderDashboardOrderItemsByStatusProviderTest::testgetOrderItemsGroupedByStatusResultFormatter` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Entity\OrderItemTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\EventListener\Doctrine\OrderWorkflowStartListenerTest::createOrder` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\EventListener\Doctrine\OrderWorkflowStartListenerTest::getSubmittedData` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::loadReturns` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::createOrder` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData::createOrderItem` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerTest::getSubmittedData` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderOnDemandWorkflowTest::testWorkflow` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Controller\OrderOnDemandWorkflowTest::getSubmittedData` [private] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Functional\Api\OrderJsonApiTest::testCreateNewOrderWithNewCustomer` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::createMarelloOrderOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::createMarelloOrderOrderItemTable` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\MarelloOrderBundleInstaller::addMarelloOrderOrderForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v1_4\MarelloOrderBundle::modifyMarelloOrderOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v1_10\MarelloOrderBundle::up` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\AddOrderPaidTemplate::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadEmailTemplatesData::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadEmailTemplatesData::getVersion` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateEmailTemplatesHtmlEscapeTags::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateEmailTemplatesProductUnit::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateOrderEmailTemplatesWithDiscountAmount::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateOrderEmailTemplatesWithShippingMethodAndType::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Migrations\Data\ORM\UpdateOrderTemplatesForWaitingForSupplyNeeds::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderItemType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\EventListener\OrderItemPurchasePriceSubscriber::onSubmit` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\EventListener\OrderTotalsSubscriber::onSubmit` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\DataTransformer\ProductToSkuTransformer::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer::__construct` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemOriginalPriceListener::setDefaultPrice` [private] Method has been removed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemOriginalPriceListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemOriginalPriceListener::__construct` [public] Method has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemOriginalPriceListener::getCalculatedPriceValue` [private] Method has been added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderOrganizationListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::setComment` [public] Method has been added.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::getComment` [public] Method has been added.
* `Marello\Bundle\OrderBundle\Entity\OrderItem::$comment` [protected] Property has been added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderDashboardController::orderitemsByStatusAction` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderDashboardController::getSubscribedServices` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Tests\Unit\Provider\OrderItemsSubtotalProviderTest` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Totals\DiscountSubtotalProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Totals\OrderTotalsProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Totals\ShippingCostSubtotalProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemsSubtotalProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Dashboard\OrderDashboardOrderItemsByStatusProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Dashboard\OrderDashboardStatisticProvider` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Dashboard\OrderStatisticsCurrencyNumberFormatter` Class was added.
* `Marello\Bundle\OrderBundle\Provider\Dashboard\OrderStatisticsCurrencyNumberProcessor` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_9\UpdateOrderItemTable` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_9\UpdateVariantHashColumnTable` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_8\MarelloOrderBundle` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_7\MarelloOrderBundle` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_6\MarelloOrderBundle` Class was added.
* `Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_6\UpdateEntityConfigExtendClassQuery` Class was added.
* `Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderItemsDiscountListener` Class was added.
* `Marello\Bundle\OrderBundle\Provider\DiscountSubtotalProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardOrderItemsByStatusProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderDashboardStatisticProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderItemsSubtotalProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderStatisticsCurrencyNumberFormatter` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderStatisticsCurrencyNumberProcessor` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\OrderTotalsProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Provider\ShippingCostSubtotalProvider` Class was removed.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalRevenueValue` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getTotalOrdersNumberValue` [public] Method parameter added.
* `Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository::getAverageOrderValue` [public] Method parameter added.

POSBundle
-----
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData::load` [public] Method implementation changed.
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData::updateDescriptionForAdmin` [private] Method has been added.
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData::updateDescriptionForUser` [private] Method has been added.
* `Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData::getVersion` [public] Method has been added.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::__construct` [public] Method parameter name changed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::__construct` [public] Method implementation changed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::authenticate` [private] Method implementation changed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::$authenticationProviderKey` [private] Property has been removed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::$authenticationProvider` [private] Property has been removed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::$translator` [private] Property has been removed.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::__construct` [public] Method parameter added.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::__construct` [public] Method parameter typing added.
* `Marello\Bundle\POSBundle\Api\Processor\HandleLogin::__construct` [public] Method parameter typing removed.

PackingBundle
-----
* `Marello\Bundle\PackingBundle\Tests\Unit\Mapper\OrderToPackingSlipMapperTest::testMap` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Tests\Unit\Entity\PackingSlipTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::createMarelloPackingSlipItemTable` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::addMarelloPackingSlipForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Controller\PackingSlipController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4_4\UpdatePackingSlipItemTable` Class was added.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4_3\UpdatePackingSlipAndItemTable` Class was added.
* `Marello\Bundle\PackingBundle\Entity\PackingSlipItem::setInventoryBatches` [public] Method parameter default added.

PaymentBundle
-----
* `Marello\Bundle\PaymentBundle\Tests\Unit\Method\Provider\ChannelPaymentMethodProviderTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodConfigRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository\PaymentMethodsConfigsRuleRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadPaymentMethodsConfigsRulesWithConfigs::setDestinations` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadPaymentMethodsConfigsRulesWithConfigs::getOrganization` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Controller\PaymentMethodsConfigsRuleControllerTest::testUpdateRemoveDestination` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Controller\PaymentMethodsConfigsRuleControllerTest::getEntityManager` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Controller\PaymentMethodsConfigsRuleControllerTest::getPaymentMethodsConfigsRuleByName` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Tests\Functional\Controller\PaymentMethodsConfigsRuleControllerTest::getPaymentMethodsConfigsRuleById` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Migrations\Schema\MarelloPaymentBundleInstaller::createMarelloPaymentPaymentTable` [protected] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider::getRepository` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\EventListener\MethodRemovalListener::getEntityManager` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\EventListener\MethodRemovalListener::getPaymentMethodConfigRepository` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Method\EventListener\MethodRemovalListener::getPaymentMethodsConfigsRuleRepository` [private] Method implementation changed.
* `Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository::disableRulesWithoutPaymentMethods` [public] Method implementation changed.
* `Marello\Bundle\PaymentBundle\MarelloPaymentBundle::getContainerExtension` [public] Method has been removed.

PaymentTermBundle
-----
* `Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures\LoadPaymentTermIntegration::getOrganization` [private] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::createDefaltPaymentRule` [private] Method has been removed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::load` [public] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::getOrganization` [private] Method implementation changed.
* `Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration::createDefaultPaymentRule` [private] Method has been added.
* `Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm::setCode` [public] Method parameter default added.

PdfBundle
-----
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Render\ConfigValuesProviderTest::testGetParamsWithScope` [public] Method implementation changed.

PricingBundle
-----
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetListOfAssembledChannelPriceLists` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetPriceListByProductSku` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testGetPriceListByChannel` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledChannelPriceListJsonApiTest::testCreateNewPriceListWithPrices` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Functional\Api\AssembledPriceListJsonApiTest::testCreateNewPriceListWithDefaultPrice` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Form\EventListener\ChannelPricingSubscriber::postSubmit` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Form\EventListener\PricingSubscriber::postSubmit` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Tests\Unit\Provider\OrderItemsSubtotalProviderTest` Class was removed.

ProductBundle
-----
* `Marello\Bundle\ProductBundle\Tests\Unit\Entity\ProductSupplierRelationTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Unit\Entity\ProductTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData::load` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData::createProduct` [private] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Controller\VariantControllerTest::testCreateVariant` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetProductById` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetListOfProducts` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testGetProductFilteredBySku` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testCreateNewProduct` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Tests\Functional\Api\ProductJsonApiTest::testUpdateProduct` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::up` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::createMarelloProductProductTable` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::createMarelloProductSupplierRelationTable` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::addMarelloProductProductForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::addFileRelations` [protected] Method has been added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_0\MarelloProductBundle::createMarelloProductProductTable` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductSupplierRelationType::__construct` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductSupplierRelationType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductTypeSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductVariantType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductsAssignSalesChannelsType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Datagrid\ProductSupplierGridListener::buildBeforeProductsBySupplier` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\EventListener\Datagrid\ProductSupplierGridListener::getProductsRelatedToSupplier` [private] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::setManufacturingCode` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::addChannel` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::addCategory` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::prePersist` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::preUpdate` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Product::clearChannels` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\Product::clearCategories` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setLeadTime` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::getLeadTime` [public] Method has been added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::$leadTime` [protected] Property has been added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::findByDataKey` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::$databaseDriver` [private] Property has been removed.
* `Marello\Bundle\ProductBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\DependencyInjection\Configuration::getConfigKeyByName` [public] Method has been added.
* `Marello\Bundle\ProductBundle\DependencyInjection\MarelloProductExtension::load` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::createStepOne` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::getAttributeFamilyCount` [private] Method has been added.
* `Marello\Bundle\ProductBundle\Api\Processor\HandleMediaUrl::process` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_15_1\AddARFileField` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_15\MarelloProductBundle` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Schema\v1_15\UpdateImportExportConfigs` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\AddARFileToDefaultFamily` Class was added.
* `Marello\Bundle\ProductBundle\Manager\ProductFileManager` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\TemplateFixture\AssembledChannelPriceListFixture` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\TemplateFixture\AssembledPriceListFixture` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\TemplateFixture\AttributeFamilyFixture` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\TemplateFixture\ProductFixture` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Strategy\AbstractAssembledPriceListStrategy` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Strategy\AssembledChannelPriceListStrategy` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Strategy\AssembledPriceListStrategy` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Strategy\ProductStrategy` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Serializer\ProductImageNormalizer` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Serializer\ProductNormalizer` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Helper\ProductAttributesHelper` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\EventListener\ProductImportExportSubscriber` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\DataConverter\AssembledChannelPriceListDataConverter` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\DataConverter\AssembledPriceListDataConverter` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\DataConverter\ProductDataConverter` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Configuration\AssembledChannelPriceListConfiguration` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Configuration\AssembledPriceListConfiguration` Class was added.
* `Marello\Bundle\ProductBundle\ImportExport\Configuration\ProductConfiguration` Class was added.
* `Marello\Bundle\ProductBundle\EventListener\CreateNotificationMessageAfterImportListener` Class was added.
* `Marello\Bundle\ProductBundle\EventListener\ProductFilesUrlListener` Class was added.
* `Marello\Bundle\ProductBundle\Async\ProductFilesUpdateProcessor` Class was added.
* `Marello\Bundle\ProductBundle\Async\Topic\ProductFilesUpdateTopic` Class was added.
* `Marello\Bundle\ProductBundle\Api\Processor\HandleLabelForFieldConfig` Class was added.
* `Marello\Bundle\ProductBundle\Api\Processor\HandlePropertyLabels` Class was added.
* `Marello\Bundle\ProductBundle\Entity\Product::setManufacturingCode` [public] Method parameter default added.
* `Marello\Bundle\ProductBundle\Entity\Product::setWeight` [public] Method parameter default added.
* `Marello\Bundle\ProductBundle\Entity\Product::setWarranty` [public] Method parameter default added.
* `Marello\Bundle\ProductBundle\Entity\Product::setPreferredSupplier` [public] Method parameter default added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setCost` [public] Method parameter default added.
* `Marello\Bundle\ProductBundle\Tests\Unit\EventListener\ProductImageListenerTest` Class was removed.
* `Marello\Bundle\ProductBundle\EventListener\ProductImageListener` Class was removed.
* `Marello\Bundle\ProductBundle\Async\ProductImageUpdateProcessor` Class was removed.
* `Marello\Bundle\ProductBundle\Async\Topic\ProductImageUpdateTopic` Class was removed.
* `Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller::addImageRelation` [protected] Method has been removed.
* `Marello\Bundle\ProductBundle\Form\Type\ProductSupplierRelationType::$localeSettings` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::getOrganization` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::setOrganization` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::setSku` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::setManufacturingCode` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::getPrice` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::setWeight` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::setWarranty` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::addCategoryCode` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::setType` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Product::$organization` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::$replenishment` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::$createdAt` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\Product::$updatedAt` [protected] Property has been removed.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setQuantityOfUnit` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setPriority` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setCost` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation::setCanDropship` [public] Method parameter typing added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::setDatabaseDriver` [public] Method has been removed.
* `Marello\Bundle\ProductBundle\Entity\ProductInterface::setSku` [public] Method parameter typing added.

PurchaseOrderBundle
-----
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::testExecuteFullReceived` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::testExecutePartial` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::testExecuteOneItemPartial` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::$doctrineHelper` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::$inventoryAllocationProvider` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::$messageProducer` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type\PurchaseOrderItemTypeTest::testSubmit` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type\PurchaseOrderItemTypeTest::submitProvider` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::testPostFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::getProduct` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::$dispatcher` [protected] Property has been added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::testAdviceCommandWillSendNotification` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\MarelloPurchaseOrderBundleInstaller::createMarelloPurchaseOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\MarelloPurchaseOrderBundleInstaller::createMarelloPurchaseOrderItemTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_2\MarelloPurchaseOrderBundle::updatePurchaseOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_3_1\MarelloPurchaseOrderBundle::updatePurchaseOrderTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Schema\v1_0\MarelloPurchaseOrderBundle::createMarelloPurchaseOrderItemTable` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Data\ORM\UpdateEmailTemplatesHtmlEscapeTags::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Migrations\Data\ORM\UpdatePurchaseOrderSupplierEmailTemplates::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateHandler::handle` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateHandler::onSuccess` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Form\Handler\PurchaseOrderCreateStepOneHandler::process` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::createPurchaseOrdersFromAllocation` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::findApplicablePurchaseOrder` [private] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::$allocationId` [private] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::$entitiesScheduledForWorkflowStart` [private] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid\PurchaseOrderGridListener::getProductsIdsInPendingPurchaseOrders` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\DependencyInjection\Configuration::getConfigTreeBuilder` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::__construct` [public] Method parameter name changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::execute` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::sendNotification` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::createStepOne` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::createStepTwo` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Exception\LogicException` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\SendScheduleChangeListener` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\SendPurchaseOrderCommand` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Workflow\Action\ReceivePurchaseOrderActionTest::$entityManager` [protected] Property has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Cron\PurchaseOrderAdviceCronTest::testAdviceCommandIsRegisteredCorrectly` [public] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::__construct` [public] Method parameter added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::getApplicableWorkflow` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::getDefaultWorkflowNames` [protected] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::__construct` [public] Method parameter added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postPersist` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderWorkflowTransitListener::postFlush` [public] Method parameter removed.
* `Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem::__construct` [public] Method has been removed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::__construct` [public] Method parameter typing added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\ApplicableWorkflowsTrait` Trait was added.

RefundBundle
-----
* `Marello\Bundle\RefundBundle\Validator\RefundBalanceValidator::validate` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Twig\RefundExtension::getBalance` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Provider\RefundBalanceTotalsProvider::getTotalWithSubtotalsValues` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Schema\MarelloRefundBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Schema\MarelloRefundBundleInstaller::createMarelloRefundItemTable` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Schema\MarelloRefundBundleInstaller::addMarelloRefundItemForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Migrations\Data\ORM\UpdateEmailTemplates::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\RefundBundle\Form\DataTransformer\TaxCodeToIdTransformer::__construct` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::calculateIndividualTaxItem` [public] Method implementation changed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::calculateBalance` [public] Method has been added.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::calculateAmount` [public] Method has been added.
* `Marello\Bundle\RefundBundle\Migrations\Schema\v1_4_2\MarelloRefundBundle` Class was added.
* `Marello\Bundle\RefundBundle\Entity\RefundItem::__construct` [public] Method has been removed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::caclulateBalance` [public] Method has been removed.
* `Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator::caclulateAmount` [public] Method has been removed.

ReportBundle
-----

ReturnBundle
-----
* `Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnData::load` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Tests\Functional\Api\ReturnJsonApiTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Tests\Functional\Api\ReturnJsonApiTest::testGetReturnById` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Migrations\Data\ORM\UpdateEmailTemplatesHtmlEscapeTags::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Migrations\Data\ORM\UpdateEmailTemplatesProductUnit::findExistingTemplate` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Form\DataTransformer\OrderToOrderNumberTransformer::__construct` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::indexAction` [public] Method implementation changed.

RuleBundle
-----
* `Marello\Bundle\RuleBundle\Tests\Unit\Entity\RuleTest::testProperties` [public] Method implementation changed.
* `Marello\Bundle\RuleBundle\Entity\Rule::setSystem` [public] Method parameter name changed.
* `Marello\Bundle\RuleBundle\Entity\Rule::setSystem` [public] Method implementation changed.
* `Marello\Bundle\RuleBundle\Entity\Rule::setExpression` [public] Method parameter default added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setName` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setEnabled` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setSortOrder` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setStopProcessing` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setExpression` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\Rule::setSystem` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setExpression` [public] Method parameter default added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setName` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setEnabled` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setSortOrder` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setStopProcessing` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setExpression` [public] Method parameter typing added.
* `Marello\Bundle\RuleBundle\Entity\RuleInterface::setSystem` [public] Method parameter typing added.

SalesBundle
-----
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::testPreRemove` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::$session` [private] Property has been removed.
* `Marello\Bundle\SalesBundle\Tests\Unit\EventListener\Doctrine\SalesChannelGroupListenerTest::$requestStack` [private] Property has been added.
* `Marello\Bundle\SalesBundle\Tests\Unit\Entity\SalesChannelGroupTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Unit\Entity\SalesChannelTest::testAccessors` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData::loadSalesChannelGroups` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData::loadSalesChannels` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Schema\MarelloSalesBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Schema\MarelloSalesBundleInstaller::createMarelloSalesChannelGroupTable` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Schema\MarelloSalesBundleInstaller::createMarelloSalesSalesChannelTable` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Schema\MarelloSalesBundleInstaller::addMarelloSalesSalesChannelForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelData::loadSalesChannels` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelGroupData::loadSalesChannelGroups` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelType::finishView` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Form\Handler\SalesChannelHandler::createOwnGroup` [private] Method implementation changed.
* `Marello\Bundle\SalesBundle\Form\EventListener\DefaultSalesChannelSubscriber::getDefaultChannels` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter name changed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method implementation changed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::prePersist` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::preUpdate` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::addProduct` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::removeProduct` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::getAssociatedSalesChannel` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setAssociatedSalesChannel` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::$associatedSalesChannel` [protected] Property has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::prePersist` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::preUpdate` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::__construct` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository::getActiveChannelsByCurrency` [public] Method has been added.
* `Marello\Bundle\SalesBundle\Migrations\Schema\v1_5\AddAssociatedSalesChannelRelation` Class was added.
* `Marello\Bundle\SalesBundle\Form\Type\SalesChannelCurrencyAwareSelectType` Class was added.
* `Marello\Bundle\SalesBundle\Form\Type\WidgetCurrencySelectType` Class was added.
* `Marello\Bundle\SalesBundle\Autocomplete\CurrencySalesChannelHandler` Class was added.
* `Marello\Bundle\SalesBundle\Autocomplete\SalesChannelForPosHandler` Class was added.
* `Marello\Bundle\SalesBundle\Api\Processor\ComputeSalesChannelAddressField` Class was added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setGroup` [public] Method parameter default added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::setDescription` [public] Method parameter default added.
* `Marello\Bundle\SalesBundle\Tests\Functional\Controller\SalesChannelGroupControllerTest` Class was removed.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter typing removed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::getOwner` [public] Method has been removed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setOwner` [public] Method has been removed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setName` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setActive` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setDefault` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setCode` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::setCurrency` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannel::$owner` [protected] Property has been removed.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::setName` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::setDescription` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelGroup::setSystem` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelType::__construct` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelType::setName` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Entity\SalesChannelType::setLabel` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Config\SalesChannelScopeManager::setScopeId` [public] Method parameter typing added.
* `Marello\Bundle\SalesBundle\Config\SalesChannelScopeManager::setScopeId` [public] Method parameter typing removed.

ShipmentBundle
-----
* `Marello\Bundle\ShipmentBundle\Migrations\Data\ORM\UpdateCurrentShipmentsWithOrganization::updateCurrentShipments` [public] Method implementation changed.

ShippingBundle
-----
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Result\ParameterBag\ParameterBagShippingMethodValidatorResultTest::testGetErrors` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Result\Factory\Common\ParameterBag\ParameterBagCommonShippingMethodValidatorResultFactoryTest::testCreateSuccessResult` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Result\Factory\Common\ParameterBag\ParameterBagCommonShippingMethodValidatorResultFactoryTest::testCreateErrorResult` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Method\Provider\Integration\ChannelShippingMethodProviderTest::setUp` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Context\ShippingContextCacheKeyGeneratorTest::testGenerateHashLineItemsOrder` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Unit\Context\ShippingContextCacheKeyGeneratorTest::testGenerateHashLineItems` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceIntegrationTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual\ManualShippingServiceIntegrationTest::testIntegrationOrder` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodConfigRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodTypeConfigRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Entity\Repository\ShippingMethodsConfigsRuleRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRulesWithConfigs::setDestinations` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRulesWithConfigs::getOrganization` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Controller\ShippingMethodsConfigsRuleControllerTest::testUpdateRemoveDestination` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Controller\ShippingMethodsConfigsRuleControllerTest::getEntityManager` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Controller\ShippingMethodsConfigsRuleControllerTest::getShippingMethodsConfigsRuleByName` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Tests\Functional\Controller\ShippingMethodsConfigsRuleControllerTest::getShippingMethodsConfigsRuleById` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::createMarelloShipmentTable` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::addMarelloShipmentForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\Provider\Integration\ChannelShippingMethodProvider::getRepository` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\EventListener\MethodAndTypeRemovalListener::getEntityManager` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\EventListener\MethodAndTypeRemovalListener::getShippingMethodConfigRepository` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\EventListener\MethodAndTypeRemovalListener::getShippingMethodTypeConfigRepository` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Method\EventListener\MethodAndTypeRemovalListener::getShippingMethodsConfigsRuleRepository` [private] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::prePersist` [public] Method has been added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::preUpdate` [public] Method has been added.
* `Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository::disableRulesWithoutShippingMethods` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::getFunctions` [protected] Method has been added.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::getFilters` [protected] Method has been added.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::getTags` [protected] Method has been added.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::getExtensions` [protected] Method has been added.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\v1_3_2\UpdateMarelloShipmentTable` Class was added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setShippingService` [public] Method parameter default added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setUpsShipmentDigest` [public] Method parameter default added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setIdentificationNumber` [public] Method parameter default added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setUpsPackageTrackingNumber` [public] Method parameter default added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setBase64EncodedLabel` [public] Method parameter default added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setShippingService` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setUpsShipmentDigest` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setIdentificationNumber` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setUpsPackageTrackingNumber` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setBase64EncodedLabel` [public] Method parameter typing added.
* `Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass::process` [public] Method has been removed.

SupplierBundle
-----
* `Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData::loadSuppliers` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\MarelloSupplierBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\MarelloSupplierBundleInstaller::createMarelloSupplierSupplierTable` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSupplierWithCurrency::updateCurrentSuppliers` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSupplierWithOrganization::updateCurrentSuppliers` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSuppliersWithCode::updateCurrentSuppliers` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Migrations\Data\ORM\UpdateCurrentSuppliersWithWarehouseAndInvLevels::updateCurrentSuppliers` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType::configureOptions` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierType::finishView` [public] Method has been added.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Controller\SupplierController::update` [protected] Method implementation changed.
* `Marello\Bundle\SupplierBundle\Validator\Constraints\SupplierEmail` Class was added.
* `Marello\Bundle\SupplierBundle\Validator\Constraints\SupplierEmailValidator` Class was added.
* `Marello\Bundle\SupplierBundle\Tests\Unit\Validator\Constraints\SupplierEmailValidatorTest` Class was added.
* `Marello\Bundle\SupplierBundle\Migrations\Schema\v1_5_2\MarelloSupplierBundle` Class was added.
* `Marello\Bundle\SupplierBundle\Form\Type\SupplierType::removeNonStreetFieldsFromAddress` [protected] Method parameter typing added.

TaxBundle
-----
* `Marello\Bundle\TaxBundle\Tests\Unit\Provider\OrderItemRowTotalsProviderTest::testProcessFormChanges` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Provider\TaxSubtotalProviderTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Provider\TaxSubtotalProviderTest::testGetSubtotal` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Provider\TaxSubtotalProviderTest::$companyReverseTaxProvider` [protected] Property has been added.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\CompositeTaxRuleMatcherTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\CompositeTaxRuleMatcherTest::testMatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\CompositeTaxRuleMatcherTest::$companyReverseTaxProvider` [protected] Property has been added.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\CountryTaxRuleMatcherTest::testMatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\RegionTaxRuleMatcherTest::testMatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Matcher\ZipCodeTaxRuleMatcherTest::testMatch` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Unit\Calculator\TaxCalculatorTest::testTaxIncluded` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData::getCountryByCode` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData::getRegionByCode` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\Controller\TaxJurisdictionControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\Controller\TaxRateControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Tests\Functional\Controller\TaxRulesControllerTest::testCreate` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Resolver\CustomerAddressItemResolver::resolve` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Provider\OrderItemRowTotalsProvider::processFormChanges` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::getSubtotal` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::fillSubtotal` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\MarelloTaxBundleInstaller::createMarelloTaxTaxCodeTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\MarelloTaxBundleInstaller::createMarelloTaxTaxRateTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\MarelloTaxBundleInstaller::createMarelloTaxTaxRuleTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\MarelloTaxBundleInstaller::createMarelloTaxJurisdictionTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\v1_3\MarelloTaxBundle::updateMarelloTaxCodeTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\v1_3\MarelloTaxBundle::updateMarelloTaxJurisdictionTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\v1_3\MarelloTaxBundle::updateMarelloTaxRuleTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Migrations\Schema\v1_3\MarelloTaxBundle::updateMarelloTaxRateTable` [protected] Method implementation changed.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::__construct` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\DependencyInjection\MarelloTaxExtension::load` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\DependencyInjection\MarelloTaxExtension::getAlias` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Controller\TaxCodeController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxJurisdictionController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRateController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Controller\TaxRuleController::indexAction` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Calculator\TaxCalculator::calculate` [public] Method implementation changed.
* `Marello\Bundle\TaxBundle\Calculator\TaxCalculator::setIsManualTaxSettingOverride` [public] Method has been added.
* `Marello\Bundle\TaxBundle\Calculator\TaxCalculator::getIsManualTaxSettingOverride` [private] Method has been added.
* `Marello\Bundle\TaxBundle\Calculator\TaxCalculator::$manualTaxOverride` [private] Property has been added.
* `Marello\Bundle\TaxBundle\Provider\CompanyReverseTaxProvider` Class was added.
* `Marello\Bundle\TaxBundle\DependencyInjection\Configuration` Class was added.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter default added.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter default added.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter default added.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter default added.
* `Marello\Bundle\TaxBundle\MarelloTaxBundle::getContainerExtension` [public] Method has been removed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::__construct` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::fillSubtotal` [protected] Method parameter added.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::$translator` [protected] Property has been removed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::$eventDispatcher` [protected] Property has been removed.
* `Marello\Bundle\TaxBundle\Provider\TaxSubtotalProvider::$taxFactory` [protected] Property has been removed.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Matcher\CompositeTaxRuleMatcher::match` [public] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Matcher\CountryTaxRuleMatcher::match` [public] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Matcher\RegionTaxRuleMatcher::match` [public] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Matcher\ZipCodeTaxRuleMatcher::match` [public] Method parameter default removed.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter name changed.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter default added.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter added.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter typing added.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter typing removed.
* `Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface::match` [public] Method parameter default removed.

UPSBundle
-----
* `Marello\Bundle\UPSBundle\Tests\Functional\EventListener\UPSTransportEntityListenerTest::testPostUpdate` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Functional\Entity\Repository\ShippingServiceRepositoryTest::setUp` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Functional\Entity\Repository\ShippingServiceRepositoryTest::findCountry` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Functional\DataFixtures\LoadChannelData::load` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRules::setDestinations` [protected] Method implementation changed.
* `Marello\Bundle\UPSBundle\Tests\Functional\DataFixtures\LoadShippingServices::load` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Migrations\Data\ORM\LoadShippingServicesData::load` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\EventListener\UPSTransportEntityListener::postUpdate` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\Controller\AjaxUPSController::getShippingServicesByCountryAction` [public] Method implementation changed.
* `Marello\Bundle\UPSBundle\MarelloUPSBundle::getContainerExtension` [public] Method has been removed.

WebhookBundle
-----
* `Marello\Bundle\WebhookBundle\Tests\Unit\Entity\WebhookTest::testProperties` [public] Method implementation changed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::up` [public] Method implementation changed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::addMarelloWebhookTable` [protected] Method implementation changed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::addMarelloWebhookForeignKeys` [protected] Method has been added.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\v1_1\MarelloWebhookBundle` Class was added.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::setExtendExtension` [public] Method has been removed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\MarelloWebhookBundleInstaller::$extendExtension` [protected] Property has been removed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\v1_0\MarelloWebhookBundle::setExtendExtension` [public] Method has been removed.
* `Marello\Bundle\WebhookBundle\Migrations\Schema\v1_0\MarelloWebhookBundle::$extendExtension` [protected] Property has been removed.

WorkflowBundle
-----
* `Marello\Bundle\WorkflowBundle\Api\Processor\HandleWorkflowTransit` Class was added.
* `Marello\Bundle\WorkflowBundle\Api\Model\WorkflowTransit` Class was added.
