<?php

namespace Marello\Bundle\FilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class ChoiceLikeFilter extends ChoiceFilter
{
    /**
     * {@inheritDoc}
     */
    protected function buildComparisonExpr(
        FilterDatasourceAdapterInterface $ds,
        $comparisonType,
        $fieldName,
        $parameterName
    ) {
        /** @var QueryBuilder $qb */
        $qb = $ds->getQueryBuilder();
        $parameter = $qb->getParameter($parameterName);
        $value = $parameter->getValue();
        if (is_array($value)) {
            $comparisonExpressions = [];
            foreach ($value as $key => $valueItem) {
                if ($key !== 0) {
                    $parameterName = sprintf('%s%d', $parameterName, $key);
                }
                $qb->setParameter($parameterName, '%|' . $valueItem . '|%');
                switch ($comparisonType) {
                    case ChoiceFilterType::TYPE_NOT_CONTAINS:
                        $comparisonExpressions[] = $ds->expr()->notLike($fieldName, $parameterName, true);
                        break;
                    default:
                        $comparisonExpressions[] = $ds->expr()->like($fieldName, $parameterName, true);
                }
            }

            return call_user_func_array([$ds->expr(), 'andX'], $comparisonExpressions);
        } else {
            switch ($comparisonType) {
                case ChoiceFilterType::TYPE_NOT_CONTAINS:
                    return $ds->expr()->notLike($fieldName, $parameterName, true);
                default:
                    return $ds->expr()->like($fieldName, $parameterName, true);
            }
        }
    }
}
