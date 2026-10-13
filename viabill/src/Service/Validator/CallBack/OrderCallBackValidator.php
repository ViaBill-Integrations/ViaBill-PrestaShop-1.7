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

        if (!$this->isOrderObjectLoaded($response)) {
            $errors[] = sprintf('Order number %s was not found', (int) $response->getOrderNumber());
        }

        if (!$this->isSignatureMatches($order)) {
            $errors[] = 'Signature did not matched';
        }

        if (!$this->isReferenceMatches($response, $order)) {
            $errors[] = sprintf(
                'References did not matched. Got %s but expected %s',
                $response->getTransaction(),
                $order->reference
            );
        }

        if (!$this->isResponseSignatureMatches($order, $response)) {
            $errors[] = 'Response signature does not match';
        }

        if (!$this->isAmountMatches($order, $response)) {
            $errors[] = sprintf(
                'Amount or currency did not match the order. Got %s %s',
                $response->getAmount(),
                $response->getCurrency()
            );
        }

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
        $currency = new \Currency($order->id_currency);
        $responseSignature = $response->getSignature();
        $expectedSignature = $this->signaturesGenerator->generateCallBackResponseSignature(
            $this->userService->getUser(),
            $order->reference,
            $order->id,
            $response->getAmount(),
            $currency->iso_code,
            $response->getStatus(),
            $response->getTime()
        );

        return is_string($responseSignature) && Config::hashEquals($expectedSignature, $responseSignature);
    }
}
