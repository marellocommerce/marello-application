- [InventoryBundle](#inventorybundle)
- [InvoiceBundle](#invoicebundle)
- [ManualShippingBundle](#manualshippingbundle)
- [NotificationMessageBundle](#notificationmessagebundle)
- [OrderBundle](#orderbundle)
- [PackingBundle](#packingbundle)
- [PdfBundle](#pdfbundle)
- [PricingBundle](#pricingbundle)
- [ProductBundle](#productbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [ReturnBundle](#returnbundle)
- [SalesBundle](#salesbundle)
- [ShippingBundle](#shippingbundle)
- [UPSBundle](#upsbundle)

InventoryBundle
-----
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::getWarehouseResults` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::cartesian` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::setEventDispatcher` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy::$eventDispatcher` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::createAllocationItems` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::getShippingAddress` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::assignDataProperties` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$propertyAccessor` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$newAllocations` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryAllocation` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::createMarelloInventoryAllocationItem` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryBalancedInventoryLevelForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryAllocationForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\MarelloInventoryBundleInstaller::addMarelloInventoryAllocationItemForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager::updateBalancedInventory` [private] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Manager\InventoryManager::getInventoryLevel` [protected] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::onAllocationComplete` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::handleInventoryUpdate` [protected] Method has been added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::setEventDispatcher` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationCompleteListener::$eventDispatcher` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::__construct` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::postFlush` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::$eventDispatcher` [private] Property has been added.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::$entities` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Entity\Allocation::getReshipmentReason` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\Allocation::setReshipmentReason` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\Allocation::$reshipmentReason` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::prePersist` [public] Method implementation changed.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::getTotalQuantity` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::setTotalQuantity` [public] Method has been added.
* `Marello\Bundle\InventoryBundle\Entity\AllocationItem::$totalQuantity` [protected] Property has been added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_9\UpdateAllocationItemTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_8\UpdateBalancedInventoryTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_7\UpdateAllocationTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_6\UpdateAllocationItemTable` Class was added.
* `Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_6\UpdateAllocationTable` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\ReshipmentItemCollectionType` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\ReshipmentItemType` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\ReshipmentReasonSelectType` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Type\ReshipmentType` Class was added.
* `Marello\Bundle\InventoryBundle\Form\Handler\ReshipmentHandler` Class was added.
* `Marello\Bundle\InventoryBundle\Factory\AllocationShippingContextFactory` Class was added.
* `Marello\Bundle\InventoryBundle\EventListener\Action\Condition\ReshipmentActionListener` Class was added.
* `Marello\Bundle\InventoryBundle\Controller\ReshipmentController` Class was added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::allocateOrderToWarehouses` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$doctrineHelper` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$warehousesProvider` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider::$eventDispatcher` [protected] Property has been removed.
* `Marello\Bundle\InventoryBundle\EventListener\Workflow\AllocationWorkflowStartListener::__construct` [public] Method parameter added.
* `Marello\Bundle\InventoryBundle\Provider\AllocationReshipmentReasonInterface` Interface was added.

InvoiceBundle
-----
* `Marello\Bundle\InvoiceBundle\Migrations\Schema\MarelloInvoiceBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Migrations\Schema\MarelloInvoiceBundleInstaller::createMarelloInvoiceInvoiceTable` [protected] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice::setSalesChannel` [public] Method implementation changed.
* `Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice::$salesChannelName` [protected] Property has been added.
* `Marello\Bundle\InvoiceBundle\Migrations\Schema\v3_1\UpdateInvoiceTable` Class was added.
* `Marello\Bundle\InvoiceBundle\Tests\Unit\Pdf\Logo\InvoiceLogoPathProviderTest` Class was removed.
* `Marello\Bundle\InvoiceBundle\Tests\Unit\Pdf\Logo\InvoiceLogoRenderParameterProviderTest` Class was removed.
* `Marello\Bundle\InvoiceBundle\Pdf\Logo\InvoiceLogoPathProvider` Class was removed.
* `Marello\Bundle\InvoiceBundle\Pdf\Logo\InvoiceLogoRenderParameterProvider` Class was removed.

ManualShippingBundle
-----
* `Marello\Bundle\ManualShippingBundle\Method\ManualShippingMethodType::createShipment` [public] Method implementation changed.

NotificationMessageBundle
-----
* `Marello\Bundle\NotificationMessageBundle\MarelloNotificationMessageBundle` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\EventListener\NotificationMessageEventListenerTest` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\Datagrid\ActionPermissionProviderTest` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Unit\Datagrid\Extension\MassAction\ResolveMassActionHandlerTest` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Functional\DataFixtures\LoadNotificationMessagesData` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Tests\Functional\Controller\NotificationMessageControllerTest` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageEntityNameProvider` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Model\ExtendNotificationMessage` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Migrations\Schema\MarelloNotificationMessageBundleInstaller` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Migrations\Schema\v1_0\MarelloNotificationMessageBundle` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Form\Type\NotificationMessageGroupConfigType` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory` Class was added.
* `Marello\Bundle\NotificationMessageBundle\EventListener\NotificationMessageEventListener` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Event\ResolveNotificationMessageEvent` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Entity\Repository\NotificationMessageRepository` Class was added.
* `Marello\Bundle\NotificationMessageBundle\DependencyInjection\Configuration` Class was added.
* `Marello\Bundle\NotificationMessageBundle\DependencyInjection\MarelloNotificationMessageExtension` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Datagrid\ActionPermissionProvider` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Datagrid\Extension\MassAction\ResolveMassAction` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Datagrid\Extension\MassAction\ResolveMassActionHandler` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Cron\NotificationMessageCleanupCommand` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Controller\AjaxNotificationMessageController` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Controller\NotificationMessageController` Class was added.
* `Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface` Interface was added.
* `Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface` Interface was added.
* `Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface` Interface was added.

OrderBundle
-----
* `Marello\Bundle\OrderBundle\Form\Type\OrderItemType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\OrderBundle\Form\Type\OrderType::addShippingAddress` [protected] Method implementation changed.
* `Marello\Bundle\OrderBundle\Controller\OrderShipmentLabelController` Class was added.

PackingBundle
-----
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Migrations\Schema\MarelloPackingBundleInstaller::createMarelloPackingSlipTable` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\Mapper\OrderToPackingSlipMapper::mapItem` [protected] Method implementation changed.
* `Marello\Bundle\PackingBundle\EventListener\Doctrine\PackingSlipItemStatusListener::prePersist` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\EventListener\Doctrine\PackingSlipItemStatusListener::onOrderShipped` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::setSalesChannel` [public] Method implementation changed.
* `Marello\Bundle\PackingBundle\Entity\PackingSlip::$salesChannelName` [protected] Property has been added.
* `Marello\Bundle\PackingBundle\Pdf\Table\PackingSlipTableProvider` Class was added.
* `Marello\Bundle\PackingBundle\Pdf\Request\PackingSlipPdfRequestHandler` Class was added.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4_2\UpdatePackingSlipTable` Class was added.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4_1\UpdatePackingSlipTable` Class was added.
* `Marello\Bundle\PackingBundle\Migrations\Data\ORM\SendPackingSlipEmailTemplate` Class was added.
* `Marello\Bundle\PackingBundle\Migrations\Schema\v1_4\UpdatePackingSlipTable` Class was removed.

PdfBundle
-----
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo\LogoPathProviderTest` Class was added.
* `Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo\LogoRenderParameterProviderTest` Class was added.
* `Marello\Bundle\PdfBundle\Tests\Unit\Mock\SalesChannelAwareModel` Class was added.
* `Marello\Bundle\PdfBundle\Provider\LogoPathProvider` Class was added.
* `Marello\Bundle\PdfBundle\Provider\Render\LogoRenderParameterProvider` Class was added.

PricingBundle
-----
* `Marello\Bundle\PricingBundle\Form\Type\ProductChannelPriceType::postSubmit` [public] Method implementation changed.
* `Marello\Bundle\PricingBundle\Form\Type\ProductPriceType::postSubmit` [public] Method implementation changed.

ProductBundle
-----
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Controller\ProductController::update` [protected] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadAllocationContextData::getVersion` [public] Method implementation changed.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadNotificationMessageResolvedData` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadNotificationMessageSourceData` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadNotificationMessageTypeData` Class was added.
* `Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadAllocationReshipmentReasonData` Class was added.
* `Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository::getPurchaseOrderItemsCandidates` [public] Method parameter added.

PurchaseOrderBundle
-----
* `Marello\Bundle\PurchaseOrderBundle\Tests\Unit\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListenerTest::getAllocation` [private] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener::postPersist` [public] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::execute` [protected] Method implementation changed.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::getOrganization` [protected] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Cron\PurchaseOrderAdviceCommand::createNotificationContext` [protected] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Controller\PurchaseOrderController::purchaseOrderCandidatesGridAction` [public] Method has been added.
* `Marello\Bundle\PurchaseOrderBundle\Provider\PurchaseOrderCandidatesProvider` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid\PurchaseOrderItemCandidatesGridListener` Class was added.
* `Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid\PurchaseOrderGridListener` Class was removed.

ReturnBundle
-----
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloProductWarranty::validateConditions` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloProductWarranty::validateProductWarranty` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloRorWarranty::validateConditions` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Manager\Rules\MarelloRorWarranty::validateProductRorWarranty` [protected] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Form\Type\ReturnType::buildForm` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Controller\ReturnController::createAction` [public] Method implementation changed.
* `Marello\Bundle\ReturnBundle\Factory\ReturnShippingContextFactory` Class was added.
* `Marello\Bundle\ReturnBundle\EventListener\Action\Condition\ReturnAllowedActionListener` Class was added.

SalesBundle
-----
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::rebalanceForSalesChannelGroup` [protected] Method implementation changed.
* `Marello\Bundle\SalesBundle\Autocomplete\AbstractMultiConditionSalesChannelHandler` Class was added.
* `Marello\Bundle\SalesBundle\Async\RebalanceSalesChannelGroupProcessor` Class was added.
* `Marello\Bundle\SalesBundle\Async\Topics` Class was added.
* `Marello\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupInventoryRebalanceListener::__construct` [public] Method parameter removed.
* `Marello\Bundle\SalesBundle\Autocomplete\ActiveSalesChannelHandler::findById` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Autocomplete\ActiveSalesChannelHandler::searchEntities` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Autocomplete\ActiveSalesChannelHandler::addSearchCriteria` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Autocomplete\StoreSalesChannelHandler::findById` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Autocomplete\StoreSalesChannelHandler::searchEntities` [protected] Method has been removed.
* `Marello\Bundle\SalesBundle\Autocomplete\StoreSalesChannelHandler::addSearchCriteria` [protected] Method has been removed.

ShippingBundle
-----
* `Marello\Bundle\ShippingBundle\Workflow\ShipmentCreateAction::executeAction` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::up` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::createMarelloShipmentTable` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::addMarelloShipmentForeignKeys` [protected] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\MarelloShippingBundleInstaller::createMarelloTrackingInfoTable` [protected] Method has been added.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::createShipment` [public] Method implementation changed.
* `Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration::createTrackingInfo` [private] Method has been added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::getTrackingInfo` [public] Method has been added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::setTrackingInfo` [public] Method has been added.
* `Marello\Bundle\ShippingBundle\Entity\Shipment::$trackingInfo` [protected] Property has been added.
* `Marello\Bundle\ShippingBundle\Model\ExtendTrackingInfo` Class was added.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\v1_3\MarelloShippingBundle` Class was added.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\v1_2\MarelloShippingBundle` Class was added.
* `Marello\Bundle\ShippingBundle\Entity\TrackingInfo` Class was added.
* `Marello\Bundle\ShippingBundle\Migrations\Schema\v1_2\MarelloShippingBundleInstaller` Class was removed.

UPSBundle
-----
* `Marello\Bundle\UPSBundle\Method\UPSShippingMethodType::createShipment` [public] Method implementation changed.


