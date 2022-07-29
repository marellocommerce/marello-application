<?php

namespace Marello\Bundle\ProductBundle\Datagrid\ORM\Query;

use Doctrine\ORM\Query\SqlWalker;

class ProductsGridSqlWalker extends SqlWalker
{
    protected $excludedFields = ['warranty'];

    /**
     * {@inheritdoc}
     */
    public function walkSelectClause($selectClause)
    {
        $select = str_replace('SELECT ', '', parent::walkSelectClause($selectClause));

        return sprintf('SELECT %s', $this->removeExcludedFields($select));
    }
    
    /**
     * {@inheritdoc}
     */
    public function walkGroupByClause($groupByClause)
    {
        $groupBy = str_replace(' GROUP BY ', '', parent::walkGroupByClause($groupByClause));

        return sprintf(' GROUP BY %s', $this->removeExcludedFields($groupBy));
    }

    /**
     * @param string $sql
     */
    private function removeExcludedFields($sql)
    {
        $sqlParts = explode(', ', $sql);
        foreach ($sqlParts as $key => $part) {
            foreach ($this->excludedFields as $field) {
                if (strpos($part, $field) !== false) {
                    unset($sqlParts[$key]);
                }
            }
        }
        return implode(', ', $sqlParts);
    }
}
