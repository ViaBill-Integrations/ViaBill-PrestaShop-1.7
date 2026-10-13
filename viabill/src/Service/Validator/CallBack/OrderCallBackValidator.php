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

namespace ViaBill\Service\Validator\CallBack;

use ViaBill\Adapter\Tools;
use ViaBill\Adapter\Validate;
use ViaBill\Config\Config;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\CallBack\CallBackResponse;
use ViaBill\Service\UserService;
use ViaBill\Util\DebugLog;
use ViaBill\Util\SignaturesGenerator;

/**
 * Class OrderCallBackValidator
 */
class OrderCallBackValidator
{
    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Logger Factory Variable Declaration.
     *
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * Signature Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * Validate Variable Declaration.
     *
     * @var Validate
     */
    private $validate;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * OrderCallBackValidator constructor.
     *
     * @param UserService $userService
     * @param Tools $tools
     * @param LoggerFactory $loggerFactory
     * @param SignaturesGenerator $signaturesGenerator
     * @param Validate $validate
     */
    public function __construct(
        UserService $userService,
        Tools $tools,
        LoggerFactory $loggerFactory,
        SignaturesGenerator $signaturesGenerator,
        Validate $validate
    ) {
        $this->tools = $tools;
        $this->loggerFactory = $loggerFactory;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->validate = $validate;
        $this->userService = $userService;
    }

    /**
     * Validates Order Callback.
     *
     * @param CallBackResponse $response
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function validate(CallBackResponse $response)
    {
        $errors = [];

        $order = new \Order((int) $response->getOrderNumber());

        // Result of each check, for the debug log.
        $checks = [];

        $checks['order found'] = $this->isOrderObjectLoaded($response);
        if (!$checks['order found']) {
            $errors[] = sprintf('Order number %s was not found', (int) $response->getOrderNumber());
        }

        $checks['callback URL key'] = $this->isSignatureMatches($order);
        if (!$checks['callback URL key']) {
            $errors[] = 'Signature did not matched';
        }

        $checks['reference'] = $this->isReferenceMatches($response, $order);
        if (!$checks['reference']) {
            $errors[] = sprintf(
                'References did not matched. Got %s but expected %s',
                $response->getTransaction(),
                $order->reference
            );
        }

        $checks['signature'] = $this->isResponseSignatureMatches($order, $response);
        if (!$checks['signature']) {
            $errors[] = 'Response signature does not match';
        }

        $checks['amount/currency'] = $this->isAmountMatches($order, $response);
        if (!$checks['amount/currency']) {
            $errors[] = sprintf(
                'Amount or currency did not match the order. Got %s %s',
                $response->getAmount(),
                $response->getCurrency()
            );
        }

        $summary = [];
        foreach ($checks as $check => $passed) {
            $summary[] = $check . ': ' . ($passed ? 'OK' : 'FAILED');
        }

        DebugLog::msg(
            'Callback validation ' . (empty($errors) ? 'SUCCEEDED' : 'FAILED') .
            ' [order: ' . (int) $response->getOrderNumber() . ']' .
            '[transaction: ' . $this->forLog($response->getTransaction()) . ']' .
            '[status: ' . $this->forLog($response->getStatus()) . '] ' .
            implode(', ', $summary),
            empty($errors) ? 'debug' : 'error'
        );

        if (!empty($errors)) {
            $logger = $this->loggerFactory->create();

            $logger->error(
                'An error occurred during callback process',
                [
                    'request' => $response,
                    'errors' => $errors,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Checks If The Callback Security Key Matches.
     *
     * The legacy (MD5 based) key is only accepted for orders created before
     * callback URLs started to carry the HMAC-SHA256 key, so payments that
     * were in progress during the upgrade still complete.
     *
     * @param \Order $order
     *
     * @return bool
     */
    public function isSignatureMatches(\Order $order)
    {
        $signature = $this->tools->getValue('key');

        if (!is_string($signature) || $signature === '') {
            return false;
        }

        if (Config::hashEquals($this->signaturesGenerator->getCallBackSecurityKey(), $signature)) {
            return true;
        }

        if (!Config::hashEquals($this->signaturesGenerator->generateLegacyCallBackSecurityKey(), $signature)) {
            return false;
        }

        $newKeySince = \Configuration::getGlobalValue(Config::CALLBACK_KEY_V2_SINCE);

        // No order has been sent with the new key yet, so every order still
        // carries the legacy key.
        if (!$newKeySince) {
            return true;
        }

        return $this->validate->isLoadedObject($order) &&
            strtotime($order->date_add) <= strtotime($newKeySince);
    }

    /**
     * Checks That The Callback Amount And Currency Match The Order.
     *
     * The amount is covered by the response signature; this is an additional
     * check that the approved amount is the one the order was placed for.
     *
     * @param \Order $order
     * @param CallBackResponse $response
     *
     * @return bool
     */
    public function isAmountMatches(\Order $order, CallBackResponse $response)
    {
        if (!$this->validate->isLoadedObject($order)) {
            return false;
        }

        $expectedAmount = $this->signaturesGenerator->formatAmount($order->total_paid_tax_incl);
        $actualAmount = $this->signaturesGenerator->formatAmount($response->getAmount());

        if ($expectedAmount !== $actualAmount) {
            return false;
        }

        $responseCurrency = trim((string) $response->getCurrency());

        // Only compare the currency when the callback provides one.
        if ($responseCurrency === '') {
            return true;
        }

        $currency = new \Currency($order->id_currency);

        return strtoupper($responseCurrency) === strtoupper($currency->iso_code);
    }

    /**
     * Checks Is Order Object Is Loaded.
     *
     * @param CallBackResponse $response
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function isOrderObjectLoaded(CallBackResponse $response)
    {
        $order = new \Order((int) $response->getOrderNumber());

        if (!$this->validate->isLoadedObject($order)) {
            return false;
        }

        return true;
    }

    /**
     * Checks If Reference Matches.
     *
     * @param CallBackResponse $response
     * @param \Order $order
     *
     * @return bool
     */
    public function isReferenceMatches(CallBackResponse $response, \Order $order)
    {
        if ($order->reference != $response->getTransaction()) {
            return false;
        }

        return true;
    }

    /**
     * Checks If Response Signature Matches.
     *
     * @param \Order $order
     * @param CallBackResponse $response
     *
     * @return bool
     */
    public function isResponseSignatureMatches(\Order $order, CallBackResponse $response)
    {
        $user = $this->userService->getUser();

        // Without a secret the signature can be computed by anyone, so it
        // proves nothing: reject the callback.
        if (trim((string) $user->getSecret()) === '') {
            DebugLog::msg('Callback signature check FAILED: no ViaBill secret key is configured (module logged out?)', 'error');

            return false;
        }

        $currency = new \Currency($order->id_currency);
        $responseSignature = $response->getSignature();
        $expectedSignature = $this->signaturesGenerator->generateCallBackResponseSignature(
            $user,
            $order->reference,
            $order->id,
            $response->getAmount(),
            $currency->iso_code,
            $response->getStatus(),
            $response->getTime()
        );

        $matches = is_string($responseSignature) && Config::hashEquals($expectedSignature, $responseSignature);

        // Log the signed values (without the secret) and only the start of
        // the signatures: enough to find the cause of a mismatch.
        $signedValues = implode('#', [
            $order->reference,
            $order->id,
            $this->signaturesGenerator->formatAmount($response->getAmount()),
            $currency->iso_code,
            $response->getStatus(),
            $response->getTime(),
        ]);
        $message = 'Callback signature check ' . ($matches ? 'OK' : 'FAILED') .
            ' [signed values: ' . $this->forLog($signedValues) . '#<secret>]' .
            '[received: ' . $this->signatureForLog($responseSignature) . ']' .
            '[expected: ' . $this->signatureForLog($expectedSignature) . ']';
        if (!$matches && is_string($responseSignature) && strtolower(trim($responseSignature)) === $expectedSignature) {
            $message .= ' (the received signature only differs in letter case or surrounding spaces)';
        }
        DebugLog::msg($message, $matches ? 'debug' : 'error');

        return $matches;
    }

    /**
     * Shortens A Signature For The Debug Log.
     *
     * @param mixed $signature
     *
     * @return string
     */
    private function signatureForLog($signature)
    {
        if (!is_string($signature) || $signature === '') {
            return 'none';
        }

        return substr($signature, 0, 12) . '...' . substr($signature, -4) . ' (' . strlen($signature) . ' chars)';
    }

    /**
     * Makes A Request Value Safe For A Single Debug Log Line.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function forLog($value)
    {
        if (!is_scalar($value)) {
            return $value === null ? 'none' : gettype($value);
        }

        return substr(preg_replace('/[\r\n\t]+/', ' ', (string) $value), 0, 200);
    }
}
