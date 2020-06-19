<?php

namespace Marello\Bundle\PaymentBundle\Model;

class Surcharge
{
    /** @var float */
    protected $paymentAmount = 0.;

    /** @var float */
    protected $handlingAmount = 0.;

    /** @var float */
    protected $discountAmount = 0.;

    /** @var float */
    protected $insuranceAmount = 0.;

    /**
     * @param float $amount
     * @return $this
     */
    public function setPaymentAmount($amount)
    {
        $this->paymentAmount = (float)$amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setHandlingAmount($amount)
    {
        $this->handlingAmount = (float)$amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getHandlingAmount()
    {
        return $this->handlingAmount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = (float)$amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setInsuranceAmount($amount)
    {
        $this->insuranceAmount = (float)$amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getInsuranceAmount()
    {
        return $this->insuranceAmount;
    }
}
