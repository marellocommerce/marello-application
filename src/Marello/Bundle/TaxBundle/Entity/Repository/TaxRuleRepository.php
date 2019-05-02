<?php

namespace Marello\Bundle\TaxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class TaxRuleRepository extends EntityRepository
{
    /**
     * @param Country $country
     * @return QueryBuilder
     */
    protected function createCountryQueryBuilder(Country $country)
    {
        $qb = $this->createQueryBuilder('taxRule');
        $qb
            ->join('taxRule.taxJurisdiction', 'taxJurisdiction')
            ->leftJoin('taxJurisdiction.zipCodes', 'zipCodes')
            ->where($qb->expr()->eq('taxJurisdiction.country', ':country'))
            ->setParameter('country', $country);

        return $qb;
    }

    /**
     * @param Country $country
     * @param Region $region
     * @param string $regionText
     * @return QueryBuilder
     */
    protected function createRegionQueryBuilder(Country $country, Region $region = null, $regionText = null)
    {
        $qb = $this->createCountryQueryBuilder($country);

        if ($region) {
            $qb->andWhere($qb->expr()->eq('taxJurisdiction.region', ':region'))
                ->setParameter('region', $region);
        } else {
            $qb->andWhere($qb->expr()->isNull('taxJurisdiction.region'));
        }

        if ($regionText) {
            $qb->andWhere($qb->expr()->eq('taxJurisdiction.regionText', ':region_text'))
                ->setParameter('region_text', $regionText);
        } else {
            $qb->andWhere($qb->expr()->isNull('taxJurisdiction.regionText'));
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $taxCodes
     */
    protected function joinTaxCodes(QueryBuilder $queryBuilder, array $taxCodes)
    {
        $queryBuilder->leftJoin('taxRule.taxCode', 'taxCode');
        $queryBuilder
            ->andWhere($queryBuilder->expr()->in('taxCode.code', ':codes'))
            ->setParameter('codes', $taxCodes);
    }
    
    /**
     * Find TaxRules by Country and TaxCodes
     *
     * @param array $taxCodes
     * @param Country $country
     * @return TaxRule[]
     */
    public function findByCountryAndTaxCode(array $taxCodes, Country $country)
    {
        $qb = $this->createRegionQueryBuilder($country);
        $qb->andWhere($qb->expr()->isNull('zipCodes.id'));
        
        $this->joinTaxCodes($qb, $taxCodes);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * Find TaxRules by Country, Region and TaxCodes
     *
     * @param array $taxCodes
     * @param Country $country
     * @param Region|null $region
     * @param null $regionText
     * @return TaxRule[]
     */
    public function findByRegionAndTaxCode(
        array $taxCodes,
        Country $country,
        Region $region = null,
        $regionText = null
    ) {
        $this->assertRegion($region, $regionText);

        $qb = $this->createRegionQueryBuilder($country, $region, $regionText);
        $qb->andWhere($qb->expr()->isNull('zipCodes.id'));

        $this->joinTaxCodes($qb, $taxCodes);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find TaxRules by ZipCode (with Region/Country check) and TaxCodes
     *
     * @param array $taxCodes
     * @param string $zipCode
     * @param Country $country
     * @param Region $region
     * @param string $regionText
     * @return TaxRule[]
     */
    public function findByZipCodeAndTaxCode(
        array $taxCodes,
        $zipCode,
        Country $country,
        Region $region = null,
        $regionText = null
    ) {
        $this->assertRegion($region, $regionText);

        $qb = $this->createRegionQueryBuilder($country, $region, $regionText);
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->lte('CAST(zipCodes.zipRangeStart as int)', ':zipCodeForRange'),
                        $qb->expr()->gte('CAST(zipCodes.zipRangeEnd as int)', ':zipCodeForRange')
                    ),
                    $qb->expr()->eq('zipCodes.zipCode', ':zipCode')
                )
            )
            ->setParameter('zipCode', $zipCode)
            ->setParameter('zipCodeForRange', (int)$zipCode);

        $this->joinTaxCodes($qb, $taxCodes);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Region|null $region
     * @param string|null $regionText
     */
    protected function assertRegion(Region $region = null, $regionText = null)
    {
        if (!$region && !$regionText) {
            throw new \InvalidArgumentException('Region or Region Text arguments missed');
        }
    }

    /**
     * @param string $taxCode
     * @param string $taxRate
     * @param string $taxJurisdiction
     * @return TaxRule|null
     */
    public function findOneByCodes($taxCode, $taxRate, $taxJurisdiction)
    {
        $qb = $this->createQueryBuilder('taxRule');
        $qb
            ->join('taxRule.taxCode', 'tc')
            ->join('taxRule.taxRate', 'tr')
            ->join('taxRule.taxJurisdiction', 'tj')
            ->andWhere('tc.code = :tc_code')
            ->andWhere('tr.code = :tr_code')
            ->andWhere('tj.code = :tj_code')
            ->setParameter('tc_code', $taxCode ? : -1)
            ->setParameter('tr_code', $taxRate ? : -1)
            ->setParameter('tj_code', $taxJurisdiction ? : -1);
        
        $results = $qb->getQuery()->getResult();
        
        return count($results) > 0 ? reset($results) : null;
    }
}
