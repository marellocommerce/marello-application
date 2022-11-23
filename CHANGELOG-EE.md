- [AddressBundle](#addressbundle)
- [DemoDataBundle](#demodatabundle)
- [InventoryBundle](#inventorybundle)
- [OrderBundle](#orderbundle)
- [PurchaseOrderBundle](#purchaseorderbundle)
- [ReplenishmentBundle](#replenishmentbundle)
- [SalesBundle](#salesbundle)

AddressBundle
-----
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\SavedCoordinates\SavedAddressCoordinatesProviderElement::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\SavedCoordinates\SavedAddressCoordinatesProviderElement::collectCoordinates` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\SavedCoordinates\SavedAddressCoordinatesProviderElement::$doctrineHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates\GeocodingApiAddressCoordinatesProviderElement::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates\GeocodingApiAddressCoordinatesProviderElement::collectCoordinates` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates\GeocodingApiAddressCoordinatesProviderElement::$doctrineHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates\GeocodingApiAddressCoordinatesProviderElement::$geocodingApiResultsProvider` [private] Property has been removed.
* `MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository::findByAddressParts` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository::$aclHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\SavedCoordinates\SavedAddressCoordinatesProviderElement::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates\GeocodingApiAddressCoordinatesProviderElement::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository::setAclHelper` [public] Method has been removed.
* `MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository::findByAddressParts` [public] Method parameter added.

DemoDataBundle
-----
* `MarelloEnterprise\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductInventoryData::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadWarehouseData::createWarehouse` [private] Method implementation changed.

InventoryBundle
-----
* `MarelloEnterprise\Bundle\InventoryBundle\MarelloEnterpriseInventoryBundle::build` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator::validate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\DefaultWarehouseExistsValidatorTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\DefaultWarehouseExistsValidatorTest::testValidateForWrongObject` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\DefaultWarehouseExistsValidatorTest::$aclHelper` [private] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\WarehouseAddedToLinkedGroupValidatorTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\WarehouseAddedToLinkedGroupValidatorTest::testValidateForWrongObject` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance\MinimumDistanceWFAStrategyTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance\MinimumDistanceWFAStrategyTest::testGetIdentifier` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance\MinimumDistanceWFAStrategyTest::testGetLabel` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance\MinimumDistanceWFAStrategyTest::testIsEnabled` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumDistance\MinimumDistanceWFAStrategyTest::testGetWarehouseResults` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::getWarehousesForOrderDataProvider` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::mockStrategy` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::$aclHelper` [protected] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\WFAStrategyChoicesProviderTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\WFAStrategyChoicesProviderTest::mockStrategy` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler\WarehouseGroupHandlerTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler\WarehouseGroupHandlerTest::mockWarehouse` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler\WarehouseGroupHandlerTest::$aclHelper` [protected] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler\WarehouseHandlerTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Form\Handler\WarehouseHandlerTest::$aclHelper` [protected] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseGroupREmoveListenerTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseGroupREmoveListenerTest::$aclHelper` [private] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseListenerTest::setUp` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine\WarehouseListenerTest::$aclHelper` [private] Property has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getWarehousesForOrder` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider::getChoices` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\InventoryBundleInstaller::getMigrationVersion` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\InventoryBundleInstaller::up` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\InventoryBundleInstaller::updateMarelloWarehouseTable` [protected] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Data\ORM\LoadMinDistanceWFARule::load` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager::updateInventoryLevel` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager::getWarehouse` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\SystemGroupGlobalWarehouseMultiSelectType::configureOptions` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::__construct` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::preSetDataListener` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::$wfaRuleRepository` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType::postSubmitDataListener` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseMultiSelectType::configureOptions` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseSelectType::configureOptions` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler::onSuccess` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler::getSystemWarehouseGroup` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseHandler::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseHandler::getSystemWarehouseGroup` [private] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Extension\InventoryLevelExtension::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Extension\WarehouseExtension::buildForm` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\EventListener\InventoryLevelSubscriber::handleUnMappedFields` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::$messageProducer` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::triggerRebalance` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::$messageProducer` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::prePersist` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::getUsedStrategies` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::findAllWFARules` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::$aclHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository::$aclHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::getDefaultExcept` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::$aclHelper` [private] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\MarelloEnterpriseInventoryExtension::load` [public] Method parameter name changed.
* `MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\MarelloEnterpriseInventoryExtension::load` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\MarelloEnterpriseInventoryExtension::getAlias` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WFARuleController::update` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WFARuleController::markMassAction` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WFARuleController::getSubscribedServices` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseChannelGroupLinkController::update` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseChannelGroupLinkController::getSubscribedServices` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseController::update` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseController::getSubscribedServices` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseGroupController::update` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\WarehouseGroupController::getSubscribedServices` [public] Method has been added.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest\WFARuleController::massAction` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest\WFARuleController::changeStatus` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest\WFARuleController::getManager` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Entity\Stub\WarehouseStub` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\MultiWHCalculator` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFA\MinimumDistance\MinimumDistanceWFAStrategy` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\InventoryAllocationProvider` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\v1_1\AddIsConsolidationWarehouseColumn` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\AllocationConsolidationExclusionSelectType` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\InventoryLevelWarehouseSelectType` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGridType` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow\TransitionEventListener` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\Configuration` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Autocomplete\InventoryLevelWarehousesSearchHandler` Class was added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\WFAStrategiesRegistryTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\MinimumQuantityWFAStrategyTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\AbstractWHCalculatorChainElementTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse\SingleWHCalculatorChainElementTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses\MultipleWHCalculatorChainElementTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\DependencyInjection\WFAStrategiesCompilerPassTest` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\MinimumQuantityWFAStrategy` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\AbstractWHCalculatorChainElement` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse\SingleWHCalculatorChainElement` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses\MultipleWHCalculatorChainElement` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WFAStrategiesCompilerPass` Class was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator::$warehouseRepository` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Provider\OrderWarehousesProviderTest::testGetWarehousesForOrder` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::setEstimation` [public] Method has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::getWarehousesForOrder` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::$strategiesRegistry` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::$rulesFiltrationService` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\OrderWarehousesProvider::$wfaRuleRepository` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\Provider\WFAStrategyChoicesProvider::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Formatter\WFAStrategyLabelFormatter::__construct` [public] Method parameter typing added.
* `MarelloEnterprise\Bundle\InventoryBundle\Formatter\WFAStrategyLabelFormatter::__construct` [public] Method parameter typing removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType::$choicesProvider` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseGroupHandler::$manager` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseHandler::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Form\Handler\WarehouseHandler::$manager` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupInventoryRebalanceListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::$translator` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::$session` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseGroupRemoveListener::$checker` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseInventoryRebalanceListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::$installed` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::$translator` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener::$session` [protected] Property has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::setAclHelper` [public] Method has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::getUsedStrategies` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository::findAllWFARules` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository::setAclHelper` [public] Method has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::setAclHelper` [public] Method has been removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository::getDefaultExcept` [public] Method parameter added.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface` Interface was removed.
* `MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface` Interface was removed.

OrderBundle
-----
* `MarelloEnterprise\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerDropshipmentTest::testAssigningOwnWarehouse` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerDropshipmentTest::testAssigningOwnAndExternalWarehouse` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\DependencyInjection\MarelloEnterpriseOrderExtension::load` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\OrderBundle\DependencyInjection\MarelloEnterpriseOrderExtension::getAlias` [public] Method has been added.
* `MarelloEnterprise\Bundle\OrderBundle\Migrations\Schema\MarelloEnterpriseOrderBundleInstaller` Class was added.
* `MarelloEnterprise\Bundle\OrderBundle\Migrations\Schema\v1_0\AddConsolidationEnabledColumn` Class was added.
* `MarelloEnterprise\Bundle\OrderBundle\DependencyInjection\Configuration` Class was added.

PurchaseOrderBundle
-----
* `MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Listener\PurchaseOrderFormViewListener::onPurchaseOrderCreateStepTwo` [public] Method implementation changed.

ReplenishmentBundle
-----
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateDestinationInventoryAction::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderAllocateOriginInventoryAction::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderCancelAction::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow\ReplenishmentOrderShipAction::handleInventoryUpdate` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine\ReplenishmentOrderItemListener::postUpdate` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::createAction` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method parameter name changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method implementation changed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::getSubscribedServices` [public] Method has been added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository\ReplenishmentOrderConfigRepository` Class was removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::createAction` [public] Method parameter added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method parameter added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method parameter typing added.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method parameter typing removed.
* `MarelloEnterprise\Bundle\ReplenishmentBundle\Controller\ReplenishmentOrderConfigController::update` [protected] Method parameter default removed.

SalesBundle
-----
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::preRemove` [public] Method implementation changed.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::__construct` [public] Method parameter added.
* `MarelloEnterprise\Bundle\SalesBundle\EventListener\Doctrine\SalesChannelGroupListener::$session` [protected] Property has been removed.

