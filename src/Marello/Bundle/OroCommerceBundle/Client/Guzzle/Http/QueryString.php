<?php

namespace Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http;

use Guzzle\Http\QueryAggregator\DuplicateAggregator;
use Guzzle\Http\QueryString as BaseQueryString;

class QueryString extends BaseQueryString
{
    /**
     * Parse a query string into a QueryString object
     *
     * @param string $query Query string to parse
     *
     * @return self
     */
    public static function fromString($query)
    {
        $q = new static();
        if ($query === '') {
            return $q;
        }

        $foundDuplicates = $foundPhpStyle = false;

        foreach (explode('&', $query) as $kvp) {
            foreach (['<=', '>=', '!=', '<', '>', '='] as $operator) {
                if (strpos($kvp, $operator) != false) {
                    $parts = explode($operator, $kvp, 2);
                    $key = rawurldecode($parts[0]);
                    if ($paramIsPhpStyleArray = substr($key, -2) == '[]') {
                        $foundPhpStyle = true;
                        $key = substr($key, 0, -2);
                    }
                    if (isset($parts[1])) {
                        $value = rawurldecode(str_replace('+', '%20', $parts[1]));
                        if (isset($q[$key])) {
                            $q->add($key, ['value' => $value, 'operator' => $operator]);
                            $foundDuplicates = true;
                        } elseif ($paramIsPhpStyleArray) {
                            $q[$key] = ['value' => [$value], 'operator' => $operator];
                        } else {
                            $q[$key] = ['value' => $value, 'operator' => $operator];
                        }
                    }
                    break;
                }
            }
        }

        // Use the duplicate aggregator if duplicates were found and not using PHP style arrays
        if ($foundDuplicates && !$foundPhpStyle) {
            $q->setAggregator(new DuplicateAggregator());
        }

        return $q;
    }

    /**
     * Convert the query string parameters to a query string string
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->data) {
            return '';
        }

        $queryString = '';

        foreach ($this->data as $name => $v) {
            if ($queryString) {
                $queryString .= $this->fieldSeparator;
            }
            $queryString .= $name;
            if ($v['value'] !== self::BLANK) {
                $queryString .= $v['operator'] . $v['value'];
            }
        }

        return $queryString;
    }
}
