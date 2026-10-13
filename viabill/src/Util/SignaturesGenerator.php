<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

namespace ViaBill\Util;

use ViaBill\Adapter\Tools;
use ViaBill\Config\Config;
use ViaBill\Object\ViaBillUser;

/**
 * Class SignaturesGenerator
 */
class SignaturesGenerator
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;
    
    /**
     * SignaturesGenerator constructor.
     *
     * @param \ViaBill $module
     * @param Tools $tools
     */
    public function __construct(\ViaBill $module, Tools $tools)
    {
        $this->module = $module;
        $this->tools = $tools;
    }
    
    /**
     * Generates Payment Check Sum.
     *
     * @param ViaBillUser $user
     * @param float $amount
     * @param string $currency
     * @param string $transaction
     * @param int $orderNumber
     * @param string $sucessUrl
     * @param string $cancelUrl
     *
     * @return string
     */
    public function generatePaymentCheckSum(
        ViaBillUser $user,
        $amount,
        $currency,
        $transaction,
        $orderNumber,
        $sucessUrl,
        $cancelUrl,
        $testMode = false
    ) {
        if ($testMode) {
            return hash( 'sha256', 
                $user->getKey() . '#' .
                $this->formatAmount($amount) . '#' .
                $currency . '#' .
                $transaction . '#' .
                $orderNumber . '#' .
                $sucessUrl . '#' .
                $cancelUrl . '#' .
                $user->getSecret() . '#true'
            );
        } else {
            return hash( 'sha256', 
                $user->getKey() . '#' .
                $this->formatAmount($amount) . '#' .
                $currency . '#' .
                $transaction . '#' .
                $orderNumber . '#' .
                $sucessUrl . '#' .
                $cancelUrl . '#' .
                $user->getSecret()
            );
        }        
    }

    /**
     * Generates Capture Signature.
     *
     * @param ViaBillUser $user
     * @param string $transaction
     * @param float|int $amount
     * @param string $currency
     *
     * @return string
     */
    public function generateCaptureSignature(ViaBillUser $user, $transaction, $amount, $currency)
    {
        return hash( 'sha256',
            $transaction . '#' .
            $user->getKey() . '#' .
            $this->formatAmount($amount) . '#' .
            $currency . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Cancel Signature.
     *
     * @param ViaBillUser $user
     * @param string $transaction
     *
     * @return string
     */
    public function generateCancelSignature(ViaBillUser $user, $transaction)
    {
        return hash( 'sha256',
            $transaction . '#' .
            $user->getKey() . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Refund Signature.
     *
     * @param ViaBillUser $user
     * @param string $transaction
     * @param float $amount
     * @param string $currency
     *
     * @return string
     */
    public function generateRefundSignature(ViaBillUser $user, $transaction, $amount, $currency)
    {
        return hash( 'sha256',
            $transaction . '#' .
            $user->getKey() . '#' .
            $this->formatAmount($amount) . '#' .
            $currency . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Call Back Security Key
     *
     * @return string
     */
    public function generateCallBackSecurityKey()
    {
        // Remember when callback URLs started to carry this key, so the legacy
        // key is only accepted for orders created before that moment.
        if (!\Configuration::getGlobalValue(Config::CALLBACK_KEY_V2_SINCE)) {
            \Configuration::updateGlobalValue(Config::CALLBACK_KEY_V2_SINCE, date('Y-m-d H:i:s'));
        }

        return $this->getCallBackSecurityKey();
    }

    /**
     * Gets The Call Back Security Key (HMAC-SHA256, Keyed With The Shop Secret).
     *
     * @return string
     */
    public function getCallBackSecurityKey()
    {
        return hash_hmac('sha256', $this->module->name . '#callback', _COOKIE_KEY_);
    }

    /**
     * Generates The Legacy (MD5 Based) Call Back Security Key.
     *
     * Only used to accept callbacks for orders created before the switch to
     * the HMAC-SHA256 key.
     *
     * @return string
     */
    public function generateLegacyCallBackSecurityKey()
    {
        return $this->tools->encrypt($this->module->name);
    }

    /**
     * Generates CallBack Response Signature.
     *
     * @param ViaBillUser $user
     * @param $transaction
     * @param $orderNumber
     * @param $amount
     * @param $currency
     * @param $status
     * @param $time
     *
     * @return string
     */
    public function generateCallBackResponseSignature(
        ViaBillUser $user,
        $transaction,
        $orderNumber,
        $amount,
        $currency,
        $status,
        $time
    ) {
        return hash( 'sha256',
            $transaction . '#' .
            $orderNumber . '#' .
            $this->formatAmount($amount) . '#' .
            $currency . '#' .
            $status . '#' .
            $time . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Status Signature.
     *
     * @param ViaBillUser $user
     * @param string $transaction
     *
     * @return string
     */
    public function generateStatusSignature(ViaBillUser $user, $transaction)
    {
        return hash( 'sha256', 
            $transaction . '#' .
            $user->getKey() . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Renew Signature.
     *
     * @param ViaBillUser $user
     * @param string $transaction
     *
     * @return string
     */
    public function generateRenewSignature(ViaBillUser $user, $transaction)
    {
        return hash( 'sha256', 
            $transaction . '#' .
            $user->getKey() . '#' .
            $user->getSecret()
        );
    }

    /**
     * Generates Main Signature.
     *
     * @param ViaBillUser $user
     *
     * @return string
     */
    public function generateSignature(ViaBillUser $user)
    {
        return hash( 'sha256', 
            $user->getKey() . '#' .
            $user->getSecret()
        );
    }

    /**
     * Format amount to 2 decimal digits by default
     */
    public function formatAmount($amount, $decimals = 2, $dec_point = '.')
    {
        // Use empty thousands separator
        return number_format((float)$amount, $decimals, $dec_point, '');
    }

}
