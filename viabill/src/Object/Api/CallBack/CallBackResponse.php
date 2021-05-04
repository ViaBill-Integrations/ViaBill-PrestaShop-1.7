<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

namespace ViaBill\Object\Api\CallBack;

/**
 * Class CallBackResponse
 *
 * @package ViaBill\Object\Api\CallBack
 */
class CallBackResponse
{
    /**
     * Callback Response Transaction Variable Declaration.
     *
     * @var string
     */
    private $transaction;

    /**
     * Callback Response Order Number Variable Declaration.
     *
     * @var string
     */
    private $orderNumber;

    /**
     * Callback Response Amount Variable Declaration.
     *
     * @var string
     */
    private $amount;

    /**
     * Callback Response Currency Variable Declaration.
     *
     * @var string
     */
    private $currency;

    /**
     * Callback Response Status Variable Declaration.
     *
     * @var string
     */
    private $status;

    /**
     * Callback Response Time Variable Declaration.
     *
     * @var string
     */
    private $time;

    /**
     * Callback Response Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * Sets Callback Response Transaction.
     *
     * @param string $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Sets Callback Response Order Number.
     *
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * Sets Callback Response Amount.
     *
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Sets Callback Response Currency.
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Sets Callback Response Status.
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Sets Callback Response Time.
     *
     * @param string $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Sets Callback Response Signature.
     *
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * Gets Callback Response Transaction.
     *
     * @return string
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Gets Callback Response Order Number.
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Gets Callback Response Amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Gets Callback Response Currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Gets Callback Response Status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets Callback Response Time.
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Gets Callback Response Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
