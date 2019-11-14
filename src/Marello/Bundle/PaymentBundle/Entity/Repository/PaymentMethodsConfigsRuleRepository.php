<?php

namespace Marello\Bundle\PaymentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Returns payment method config rules by destination, currency and website
 */
class PaymentMethodsConfigsRuleRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param AddressInterface $billingAddress
     * @param string           $currency
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getByDestinationAndCurrency(
        AddressInterface $billingAddress,
        string $currency
    ): array {
        $queryBuilder = $this->getByCurrencyQueryBuilder($currency)
            ->leftJoin('methodsConfigsRule.destinations', 'destination')
            ->leftJoin('methodsConfigsRule.rule', 'rule')
            ->addSelect('rule', 'destination', 'postalCode')
            ->leftJoin('destination.region', 'region')
            ->leftJoin('destination.postalCodes', 'postalCode')
            ->andWhere('destination.country = :country or destination.country is null')
            ->andWhere('region.code = :regionCode or region.code is null')
            ->andWhere('postalCode.name in (:postalCodes) or postalCode.name is null')
            ->setParameter('country', $billingAddress->getCountryIso2())
            ->setParameter('regionCode', $billingAddress->getRegionCode())
            ->setParameter('postalCodes', explode(',', $billingAddress->getPostalCode()));

        return $this->aclHelper->apply($queryBuilder)->getResult();
    }

    /**
     * @param string $currency
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getByCurrency(string $currency): array
    {
        $query = $this->getByCurrencyQueryBuilder($currency);

        return $this->aclHelper->apply($query)->getResult();
    }

    /**
     * @param string $currency
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getByCurrencyWithoutDestination(string $currency): array
    {
        $query = $this->getByCurrencyQueryBuilder($currency)
            ->leftJoin('methodsConfigsRule.destinations', 'destination')
            ->andWhere('destination.id is null');

        return $this->aclHelper->apply($query)->getResult();
    }

    /**
     * @param string $methodId
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getConfigsWithEnabledRuleAndMethod($methodId)
    {
        $query = $this->createQueryBuilder('methodsConfigsRule')
            ->innerJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->innerJoin('methodsConfigsRule.rule', 'rule')
            ->andWhere('rule.enabled = true')
            ->andWhere('methodConfigs.method = :methodId')
            ->setParameter('methodId', $methodId);

        return $this->aclHelper->apply($query)->getResult();
    }

    /**
     * @param bool $onlyEnabled
     *
     * @return mixed
     */
    public function getRulesWithoutPaymentMethods($onlyEnabled = false)
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

    public function disableRulesWithoutPaymentMethods()
    {
        $rules = $this->getRulesWithoutPaymentMethods(true);
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
    private function getByCurrencyQueryBuilder($currency): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('methodsConfigsRule');

        return $queryBuilder
            ->leftJoin('methodsConfigsRule.methodConfigs', 'methodConfigs')
            ->where('methodsConfigsRule.currency = :currency')
            ->orderBy($queryBuilder->expr()->asc('methodsConfigsRule.id'))
            ->setParameter('currency', $currency);
    }

    /**
     * @param string $method
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getRulesByMethod($method)
    {
        $qb = $this->getRulesByMethodQueryBuilder($method);

        return $this->aclHelper->apply($qb)->getResult();
    }

    /**
     * @param string $method
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getEnabledRulesByMethod($method)
    {
        $qb = $this->getRulesByMethodQueryBuilder($method)
            ->innerJoin('methodsConfigsRule.rule', 'rule', Expr\Join::WITH, 'rule.enabled = true');

        return $this->aclHelper->apply($qb)->getResult();
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
