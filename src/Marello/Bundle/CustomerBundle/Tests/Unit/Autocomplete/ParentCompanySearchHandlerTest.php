<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Autocomplete;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\CustomerBundle\Autocomplete\ParentCompanySearchHandler;
use Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ParentCompanySearchHandlerTest extends TestCase
{
    const TEST_ENTITY_CLASS = 'TestEntity';

    /**
     * @var ParentCompanySearchHandler
     */
    protected $searchHandler;

    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $managerRegistry;

    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityManager;

    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityRepository;

    /**
     * @var Indexer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexer;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    protected function setUp(): void
    {
        $this->entityRepository = $this
            ->getMockBuilder(CompanyRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataFactory = $this->getMetaMocks();
        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($this->entityRepository));

        $this->managerRegistry = $this->createMock('Doctrine\Persistence\ManagerRegistry');
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($this->entityManager));
        $this->indexer = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\Indexer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $searchMappingProvider = $this->createMock(SearchMappingProvider::class);
        $searchMappingProvider->expects($this->once())
            ->method('getEntityAlias')
            ->with(self::TEST_ENTITY_CLASS)
            ->willReturn('alias');

        $this->searchHandler = new ParentCompanySearchHandler(self::TEST_ENTITY_CLASS, ['name']);
        $this->searchHandler->initSearchIndexer($this->indexer, $searchMappingProvider);
        $this->searchHandler->initDoctrinePropertiesByManagerRegistry($this->managerRegistry);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->searchHandler->setPropertyAccessor($propertyAccessor);
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     * @param string $query
     */
    public function testSearchNoSeparator($query)
    {
        $this->indexer->expects($this->never())
            ->method($this->anything());
        $result = $this->searchHandler->search($query, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('more', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertFalse($result['more']);
        $this->assertEmpty($result['results']);
    }

    /**
     * @return array
     */
    public function queryWithoutSeparatorDataProvider()
    {
        return [
            [''],
            ['test']
        ];
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     * @param string $search
     */
    public function testSearchNewCustomer($search)
    {
        $page = 1;
        $perPage = 15;
        $queryString = $search . ';';

        $foundElements = [
            $this->getSearchItem(1),
            $this->getSearchItem(2)
        ];
        $resultData = [
            $this->getResultStub(1, 'test1'),
            $this->getResultStub(2, 'test2')
        ];
        $expectedResultData = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2']
        ];
        $expectedIds = [1, 2];

        $this->assertSearchCall($search, $page, $perPage, $foundElements, $resultData, $expectedIds);

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertEquals($expectedResultData, $searchResult['results']);
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     * @param string $search
     */
    public function testSearchExistingCustomer($search)
    {
        $page = 1;
        $perPage = 15;
        $customerId = 2;
        $queryString = $search . ';' . $customerId;

        $foundElements = [
            $this->getSearchItem(1),
            $this->getSearchItem($customerId)
        ];
        $resultData = [
            $this->getResultStub(1, 'test1')
        ];
        $expectedResultData = [
            ['id' => 1, 'name' => 'test1']
        ];
        $expectedIds = [1];

        $this->entityRepository->expects($this->once())
            ->method('getChildrenIds')
            ->with($customerId, $this->anything())
            ->will($this->returnValue([]));

        $this->assertSearchCall($search, $page, $perPage, $foundElements, $resultData, $expectedIds);

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertEquals($expectedResultData, $searchResult['results']);
    }

    /**
     * @dataProvider queryWithoutSeparatorDataProvider
     * @param string $search
     */
    public function testSearchExistingCustomerWithChildren($search)
    {
        $page = 1;
        $perPage = 15;
        $customerId = 2;
        $queryString = $search . ';' . $customerId;
        $foundElements = [
            $this->getSearchItem(1),
            $this->getSearchItem(3)
        ];
        $resultData = [
            $this->getResultStub(1, 'test1')
        ];
        $expectedResultData = [
            ['id' => 1, 'name' => 'test1']
        ];
        $expectedIds = [1];

        $this->entityRepository->expects($this->once())
            ->method('getChildrenIds')
            ->with($customerId, $this->anything())
            ->will($this->returnValue([3]));

        $this->assertSearchCall($search, $page, $perPage, $foundElements, $resultData, $expectedIds);

        $searchResult = $this->searchHandler->search($queryString, $page, $perPage);
        $this->assertIsArray($searchResult);
        $this->assertArrayHasKey('more', $searchResult);
        $this->assertArrayHasKey('results', $searchResult);
        $this->assertEquals($expectedResultData, $searchResult['results']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMetaMocks()
    {
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->onlyMethods(['getSingleIdentifierFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));
        $metadataFactory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->onlyMethods(['getMetadataFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($metadata));

        return $metadataFactory;
    }

    /**
     * @param int $id
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSearchItem($id)
    {
        $element = $this->getMockBuilder('Oro\Bundle\SearchBundle\Query\Result\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $element->expects($this->once())
            ->method('getRecordId')
            ->will($this->returnValue($id));

        return $element;
    }

    /**
     * @param int $id
     * @param string $name
     * @return \stdClass
     */
    protected function getResultStub($id, $name)
    {
        $result = new \stdClass();
        $result->id = $id;
        $result->name = $name;

        return $result;
    }

    /**
     * @param string $search
     * @param int $page
     * @param int $perPage
     * @param array $foundElements
     * @param array $resultData
     * @param array $expectedIds
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function assertSearchCall(
        $search,
        $page,
        $perPage,
        array $foundElements,
        array $resultData,
        array $expectedIds
    ) {
        $searchResult = $this->getMockBuilder('Oro\Bundle\SearchBundle\Query\Result')
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult->expects($this->once())
            ->method('getElements')
            ->will($this->returnValue($foundElements));
        $this->indexer->expects($this->once())
            ->method('simpleSearch')
            ->with($search, $page - 1, $perPage + 1, 'alias')
            ->will($this->returnValue($searchResult));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getResult'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($resultData));

        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();
        $expr->expects($this->once())
            ->method('in')
            ->with('e.id', ':entityIds')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('entityIds', $expectedIds)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($expr));
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with($expr)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $this->entityRepository
            ->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        return $searchResult;
    }
}
