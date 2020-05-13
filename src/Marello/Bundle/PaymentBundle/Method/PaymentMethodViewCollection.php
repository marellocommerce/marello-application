<?php

namespace Marello\Bundle\PaymentBundle\Method;

class PaymentMethodViewCollection
{
    const TYPES_FIELD = 'types';

    /**
     * @var array
     */
    private $methodViews = [];

    /**
     * @param string $methodId
     * @param array $methodView
     *
     * @return $this
     */
    public function addMethodView($methodId, array $methodView)
    {
        if ($this->hasMethodView($methodId)) {
            return $this;
        }

        $this->methodViews[$methodId] = $methodView;

        return $this;
    }

    /**
     * @param string $methodId
     *
     * @return bool
     */
    public function hasMethodView($methodId)
    {
        return array_key_exists($methodId, $this->methodViews);
    }

    /**
     * @param string $methodId
     *
     * @return $this
     */
    public function removeMethodView($methodId)
    {
        if (false === $this->hasMethodView($methodId)) {
            return $this;
        }

        unset($this->methodViews[$methodId]);

        return $this;
    }

    /**
     * @param string $methodId
     *
     * @return array|null
     */
    public function getMethodView($methodId)
    {
        if (false === $this->hasMethodView($methodId)) {
            return null;
        }

        return $this->methodViews[$methodId];
    }

    /**
     * @return array
     */
    public function getAllMethodsViews()
    {
        return $this->methodViews;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $resultingFullMethodViews = [];

        foreach ($this->methodViews as $methodId => $methodView) {
            if (false === array_key_exists($methodId, $resultingFullMethodViews)) {
                $resultingFullMethodViews[$methodId] = $methodView;
            }
        }

        uasort(
            $resultingFullMethodViews,
            function ($methodData1, $methodData2) {
                if (false === array_key_exists('sortOrder', $methodData1)
                    || false === array_key_exists('sortOrder', $methodData2)
                ) {
                    throw new \Exception('Method View should contain sortOrder');
                }

                return $methodData1['sortOrder'] - $methodData2['sortOrder'];
            }
        );

        return $resultingFullMethodViews;
    }

    /**
     * @return self
     */
    public function clear()
    {
        $this->methodViews = [];

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->methodViews) <= 0;
    }
}
