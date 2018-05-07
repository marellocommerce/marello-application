<?php

namespace Marello\Bridge\MarelloOroCommerce\Provider;

use Doctrine\Common\Inflector\Inflector;

use Oro\Bundle\EntityBundle\Model\EntityAlias;
use Oro\Bundle\EntityBundle\Provider\EntityAliasProviderInterface;

class MarelloEntityAliasProvider implements EntityAliasProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityAlias($entityClass)
    {
        if ($this->isMarelloEntity($entityClass)) {
            $name = $this->getEntityName($entityClass);

            return new EntityAlias(
                strtolower($name),
                strtolower(Inflector::pluralize($name))
            );
        }
    }
    
    /**
     * Determines whether the given entity is from one of Marello bundles.
     *
     * @param string $entityClass
     *
     * @return bool
     */
    protected function isMarelloEntity($entityClass)
    {
        return $this->startsWith($entityClass, 'Marello');
    }
    
    /**
     * Determines whether the beginning of $haystack matches $needle.
     *
     * @param string $haystack The string to check
     * @param string $needle   The string to compare
     *
     * @return bool
     */
    protected function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
    
    /**
     * Gets the short name of the class, the part without the namespace.
     *
     * @param string $className The full name of a class
     *
     * @return string
     */
    protected function getShortClassName($className)
    {
        $lastDelimiter = strrpos($className, '\\');

        return false === $lastDelimiter
            ? $className
            : substr($className, $lastDelimiter + 1);
    }

    /**
     * Returns a string which is used to build entity aliases for entities from Marello bundles
     *
     * @param string $entityClass
     *
     * @return string
     */
    protected function getEntityName($entityClass)
    {
        return sprintf('marello_%s', $this->getShortClassName($entityClass));
    }
}
