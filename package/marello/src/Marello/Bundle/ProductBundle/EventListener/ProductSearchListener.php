<?php

namespace Marello\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SearchBundle\Event\BeforeSearchEvent;
use Oro\Bundle\SearchBundle\Provider\AbstractSearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;

class ProductSearchListener
{
    /**
     * @var AbstractSearchMappingProvider
     */
    private $mappingProvider;

    /**
     * @param AbstractSearchMappingProvider $mappingProvider
     */
    public function __construct(
        AbstractSearchMappingProvider $mappingProvider
    ) {
        $this->mappingProvider = $mappingProvider;
    }

    /**
     * @param BeforeSearchEvent $event
     */
    public function process(BeforeSearchEvent $event)
    {
        $this->applyQueryRestrictions($event->getQuery());
    }

    /**
     * Run ProductsManager restriction over the search query
     *
     * @param Query $query
     */
    private function applyQueryRestrictions(Query $query)
    {
        if (!$this->isProductInFrom($query)) {
            return;
        }
        
        $criteria = $query->getCriteria();
        $this->check($criteria->getWhereExpression(), $criteria);
    }

    /**
     * @param Expression $expression
     * @param Criteria $criteria
     */
    private function check(Expression $expression, Criteria $criteria)
    {
        if ($expression instanceof CompositeExpression) {
            foreach ($expression->getExpressionList() as $k => $child) {
                $this->check($child, $criteria);
            }
        } elseif ($expression instanceof Comparison) {
            if ($expression->getOperator() === Comparison::CONTAINS &&
                !preg_match('/\s/', $expression->getValue()->getValue())) {
                $likeComparison = new Comparison(
                    $expression->getField(),
                    \Oro\Bundle\SearchBundle\Query\Criteria\Comparison::LIKE,
                    $expression->getValue()
                );
                $criteria->andWhere($likeComparison);
            }
        }
    }

    /**
     * @param Query $query
     * @return bool
     */
    private function isProductInFrom(Query $query)
    {
        $productEntityAlias =
            $this->mappingProvider->getEntityAlias(Product::class);

        $allowedEntries = [
            $productEntityAlias,
            Product::class,
            '*'
        ];

        $from = $query->getFrom();

        $result = array_intersect($from, $allowedEntries);

        return !empty($result);
    }
}
