<?php

namespace Marello\Bundle\OrderBundle\Context;

use Symfony\Component\Form\FormInterface;

interface OrderItemContextInterface
{
    /**
     * @return int
     */
    public function getSalesChannelId();

    /**
     * @param int $salesChannelId
     * @return $this
     */
    public function setSalesChannelId($salesChannelId);

    /**
     * @return array
     */
    public function getSubmittedData();

    /**
     * @param array $submittedData
     * @return $this
     */
    public function setSubmittedData(array $submittedData);

    /**
     * @return array
     */
    public function getResult();

    /**
     * @param array $result
     * @return $this
     */
    public function setResult(array $result);
}
