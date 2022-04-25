<?php

namespace Marello\Bundle\ShippingBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

class ShippingMethodsConfigsRuleRepository extends ServiceEntityRepository
{
    /**
     * @param AddressInterface $shippingAddress
     * @param string $currency
     * @param AclHelper $aclHelper
     *
     * @return ShippingMethodsConfigsRule[]
     */
    public function getByDestinationAndCurrency(AddressInterface $shippingAddress, $currency, AclHelper $aclHelper)
    {
        $query = $this->getByCurrencyQuery($currency)
            ->leftJoin('methodsConfigsRule.destinations', 'destination')
            ->leftJoin('destination.region', 'region')
            ->leftJoin('destination.postalCodes', 'postalCode')
            ->andWhere('destination.country = :country or destination.country is null')
            ->andWhere('region.code = :regionCode or region.code is null')
            ->andWhere('postalCode.name in (:postalCodes) or postalCode.name is null')
            ->setParameter('country', $shippingAddress->getCountryIso2())
            ->setParameter('regionCode', $shippingAddress->getRegionCode())
            ->setParameter('postalCodes', explode(',', $shippingAddress->getPostalCode()));

        return $aclHelper->apply($query)->getResult();
    }

    /**
     * @param string $currency
     * @param AclHelper $aclHelper
     * @return ShippingMethodsConfigsRule[]
     */
    public function getByCurrency($currency, AclHelper $aclHelper)
    {
        $query = $this->getByCurrencyQuery($currency);

        return $aclHelper->apply($query)->getResult();
    }

    /**
     * @param string $currency
     * @param AclHelper $aclHelper
     * @return ShippingMethodsConfigsRule[]
     */
    public function getByCurrencyWithoutDestination($currency, AclHelper $aclHelper)
    {
        $query = $this->getByCurrencyQuery($currency)
            ->leftJoin('methodsConfigsRule.destinations', 'destination')
            ->andWhere('destination.id is null');

        return $aclHelper->apply($query)->getResult();
    }

    /**
     * @param string $methodId
     * @param AclHelper $aclHelper
     * @return ShippingMethodsConfigsRule[]
     */
    public function getConfigsWithEnabledRuleAndMethod($methodId, AclHelper $aclHelper)
    {
        $query = $this->createQueryBuilder('methodsConfigsRule')
            ->innerJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->innerJoin('methodsConfigsRule.rule', 'rule')
            ->andWhere('rule.enabled = true')
            ->andWhere('methodConfigs.method = :methodId')
            ->setParameter('methodId', $methodId);

        return $aclHelper->apply($query)->getResult();
    }

    /**
     * @param bool $onlyEnabled
     *
     * @return mixed
     */
    public function getRulesWithoutShippingMethods($onlyEnabled = false)
    {
        $qb = $this->createQueryBuilder('methodsConfigsRule')
            ->select('rule.id')
            ->leftJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->leftJoin('methodsConfigsRule.rule', 'rule');
        if ($onlyEnabled) {
            $qb->andWhere('rule.enabled = true');
        }

        return $qb
            ->having('COUNT(methodConfigs.id) = 0')
            ->groupBy('rule.id')
            ->getQuery()->execute();
    }

    public function disableRulesWithoutShippingMethods()
    {
        $rules = $this->getRulesWithoutShippingMethods(true);
        if (0 < count($rules)) {
            $enabledRulesIds = array_column($rules, 'id');
            $qb = $this->createQueryBuilder('methodsConfigsRule');
            $qb->update('MarelloRuleBundle:Rule', 'rule')
                ->set('rule.enabled', ':newValue')
                ->setParameter('newValue', false)
                ->where($qb->expr()->in('rule.id', ':rules'))
                ->setParameter('rules', $enabledRulesIds)
                ->getQuery()->execute();
        }
    }

    /**
     * @param string $currency
     *
     * @return QueryBuilder
     */
    private function getByCurrencyQuery($currency)
    {
        $queryBuilder = $this->createQueryBuilder('methodsConfigsRule');

        return $queryBuilder
            ->addSelect('methodConfigs', 'typeConfigs')
            ->leftJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->leftJoin('methodConfigs.typeConfigs', 'typeConfigs')
            ->where('methodsConfigsRule.currency = :currency')
            ->setParameter('currency', $currency)
            ->orderBy($queryBuilder->expr()->asc('methodsConfigsRule.id'));
    }

    /**
     * @param string $method
     * @param AclHelper $aclHelper
     * @return ShippingMethodsConfigsRule[]
     */
    public function getRulesByMethod($method, AclHelper $aclHelper)
    {
        $qb = $this->getRulesByMethodQueryBuilder($method);

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param string $method
     * @param AclHelper $aclHelper
     * @return ShippingMethodsConfigsRule[]
     */
    public function getEnabledRulesByMethod($method, AclHelper $aclHelper)
    {
        $qb = $this->getRulesByMethodQueryBuilder($method)
            ->innerJoin('methodsConfigsRule.rule', 'rule', Expr\Join::WITH, 'rule.enabled = true');

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param string $method
     *
     * @return QueryBuilder
     */
    private function getRulesByMethodQueryBuilder($method)
    {
        return $this->createQueryBuilder('methodsConfigsRule')
            ->innerJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->where('methodConfigs.method = :method')
            ->setParameter('method', $method);
    }
}
