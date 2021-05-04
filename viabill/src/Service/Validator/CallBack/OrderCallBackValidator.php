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

namespace ViaBill\Service\Validator\CallBack;

use ViaBill\Adapter\Tools;
use ViaBill\Adapter\Validate;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\CallBack\CallBackResponse;
use ViaBill\Service\UserService;
use ViaBill\Util\SignaturesGenerator;

/**
 * Class OrderCallBackValidator
 *
 * @package ViaBill\Service\Validator\CallBack
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
        $errors = array();
        if (!$this->isSignatureMatches()) {
            $errors[] = 'Signature did not matched';
        }

        if (!$this->isOrderObjectLoaded($response)) {
            $errors[] = sprintf('Order number %s was not found', (int) $response->getOrderNumber());
        }

        $order = new \Order((int) $response->getOrderNumber());

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

        if (!empty($errors)) {
            $logger = $this->loggerFactory->create();

            $logger->error(
                'An error occurred during callback process',
                array(
                    'request' => $response,
                    'errors' => $errors
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Checks If Signature Matches.
     *
     * @return bool
     */
    public function isSignatureMatches()
    {
        $signature = $this->tools->getValue('key');

        if ($signature == $this->signaturesGenerator->generateCallBackSecurityKey()) {
            return true;
        }

        return false;
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

        return $responseSignature === $expectedSignature;
    }
}
