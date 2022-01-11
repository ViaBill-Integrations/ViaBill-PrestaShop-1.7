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

namespace ViaBill\Service\Handler;

use Order;
use ViaBill;
use ViaBill\Adapter\Configuration;
use ViaBill\Adapter\Tools;
use ViaBill\Adapter\Validate;
use ViaBill\Config\Config;
use ViaBill\Factory\LoggerFactory;
use ViaBill\Object\Api\Capture\CaptureRequest;
use ViaBill\Object\Handler\HandlerResponse;
use ViaBill\Service\Api\Capture\CaptureService;
use ViaBill\Service\UserService;
use ViaBill\Util\DebugLog;
use ViaBill\Util\SignaturesGenerator;

/**
 * Class CapturePaymentHandler
 */
class CapturePaymentHandler
{
    /**
     * Filename Constant
     */
    const FILENAME = 'CapturePaymentHandler';

    /**
     * Capture Services Variable Declaration.
     *
     * @var CaptureService
     */
    private $captureService;

    /**
     * User Service Variable Declaration.
     *
     * @var UserService
     */
    private $userService;

    /**
     * Signature Generator Variable Declaration.
     *
     * @var SignaturesGenerator
     */
    private $signaturesGenerator;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    private $module;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * Validate Variable Declaration.
     *
     * @var Validate
     */
    private $validate;

    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Logger Factory Variable Declaration.
     *
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * CapturePaymentHandler constructor.
     *
     * @param ViaBill $module
     * @param LoggerFactory $loggerFactory
     * @param CaptureService $captureService
     * @param UserService $userService
     * @param SignaturesGenerator $signaturesGenerator
     * @param Tools $tools
     * @param Validate $validate
     * @param Configuration $configuration
     */
    public function __construct(
        ViaBill $module,
        LoggerFactory $loggerFactory,
        CaptureService $captureService,
        UserService $userService,
        SignaturesGenerator $signaturesGenerator,
        Tools $tools,
        Validate $validate,
        Configuration $configuration
    ) {
        $this->captureService = $captureService;
        $this->userService = $userService;
        $this->signaturesGenerator = $signaturesGenerator;
        $this->module = $module;
        $this->tools = $tools;
        $this->validate = $validate;
        $this->configuration = $configuration;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * Handles Payment Capture.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return HandlerResponse
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handle(Order $order, $amount)
    {
        // debug info
        $debug_str = (empty($order)) ? 'Order is empty' : var_export($order, true);
        DebugLog::msg("Capture Payment Handle / Amount: $amount Order: $debug_str", 'notice');

        $errors = $this->validateCapturePayment($order, $amount);

        if (!empty($errors)) {
            $debug_str = var_export($errors, true);
            DebugLog::msg("Capture Payment Handle / Validation Errors: $debug_str", 'error');

            return new HandlerResponse($order, 500, $errors);
        }

        $currency = new \Currency($order->id_currency);

        $user = $this->userService->getUser();
        $reference = $order->reference;
        $amountNegative = -1 * abs($amount);

        $signature = $this->signaturesGenerator->generateCaptureSignature(
            $user,
            $reference,
            $amountNegative,
            $currency->iso_code
        );

        try {
            $debug_str = '';
            $debug_str .= (!empty($reference)) ? '[Order Ref: ' . $reference . ']' : '[No order reference]';
            $debug_str .= (method_exists($user, 'getKey')) ? '[Key: ' . $user->getKey() . ']' : '[No user key]';
            $debug_str .= (!empty($signature)) ? '[Signature: ' . $signature . ']' : '[No signature]';
            $debug_str .= (!empty($amountNegative)) ? '[Amount Negative: ' . $amountNegative . ']' : '[No amount negative]';
            $debug_str .= (property_exists($currency, 'iso_code')) ? '[Currency Code: ' . $currency->iso_code . ']' : '[No currency code]';
            DebugLog::msg("Capture Payment Handle / Request params: $debug_str", 'notice');
        } catch (\Exception $exception) {
            $er = $exception->getMessage();
            $debug_str = var_export($er, true);
            DebugLog::msg("Capture Payment Handle / Request exception: $debug_str", 'error');
        }

        $captureRequest = new CaptureRequest(
            $reference,
            $user->getKey(),
            $signature,
            $amountNegative,
            $currency->iso_code
        );

        $captureResponse = $this->captureService->captureTransaction($captureRequest);

        $responseErrors = $captureResponse->getErrors();

        if (!empty($responseErrors)) {
            $debug_str = var_export($responseErrors, true);
            DebugLog::msg("Capture Payment Handle / Response Errors: $debug_str", 'error');
        }

        $isMarked = false;
        if (!empty($responseErrors)) {
            foreach ($responseErrors as $error) {
                $errors[] = $error->getError();
            }
        } else {
            $isMarked = $this->markCapturedPayment($order, $amount);
        }

        $warnings = [];

        if (!$isMarked && empty($responseErrors)) {
            $warningMessage =
            $this->module->l('The total amount of %s has been captured but has not been marked.', self::FILENAME);

            $warnings[] = sprintf(
                $warningMessage,
                $this->tools->displayPrice($amount, $currency)
            );
        }

        $message = sprintf(
            $this->module->l('Successfully captured total amount of %s', self::FILENAME),
            $this->tools->displayPrice($amount, $currency)
        );

        $debug_str = '';
        if (!empty($message)) {
            $debug_str .= '[Message: ' . var_export($message, true) . ']';
        }
        if (!empty($errors)) {
            $debug_str .= '[Errors: ' . var_export($errors, true) . ']';
        }
        if (!empty($warnings)) {
            $debug_str .= '[Warnings: ' . var_export($warnings, true) . ']';
        }
        $debug_str .= (method_exists($captureResponse, 'getStatusCode')) ? '[Status code: ' . $captureResponse->getStatusCode() . ']' : '[No status code]';
        DebugLog::msg("Capture Payment Handle / Response Handler Params: $debug_str", 'notice');

        return new HandlerResponse(
            $order,
            $captureResponse->getStatusCode(),
            $errors,
            $message,
            $warnings
        );
    }

    /**
     * Validates Payment Capture.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function validateCapturePayment(Order $order, $amount)
    {
        if (!$this->validate->isUnsignedFloat($amount)) {
            $message =
                $this->module->l(
                    'Incorrect value provided for capture fields. Expected unsigned number got %s',
                    self::FILENAME
                );

            return [
                sprintf(
                    $message,
                    $amount
                ),
            ];
        }

        $primaryKey = \ViaBillOrderCapture::getPrimaryKey($order->id);
        $orderCapture = new \ViaBillOrderCapture($primaryKey);

        $capturedAmount = $orderCapture->getTotalCaptured();

        $currency = new \Currency($order->id_currency);

        $total = $capturedAmount + (float) $amount;

        if ($this->tools->displayNumber($total) > $this->tools->displayNumber($order->total_paid_tax_incl)) {
            $message =
            $this->module->l('Total amount of capture breached. Remaining left to capture is %s', self::FILENAME);

            $remainingToCapture = (float) $order->total_paid_tax_incl - $capturedAmount;

            return [
                sprintf(
                    $message,
                    $this->tools->displayPrice($remainingToCapture, $currency)
                ),
            ];
        }

        return [];
    }

    /**
     * Marks Captured Payment.
     *
     * @param Order $order
     * @param float $amount
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function markCapturedPayment(Order $order, $amount)
    {
        $orderCapture = new \ViaBillOrderCapture();
        $orderCapture->id_order = $order->id;
        $orderCapture->amount = $amount;

        $debug_str = 'markCapturedPayment / order ID:' . $order->id . ' amount: ' . $amount;
        DebugLog::msg($debug_str, 'notice');

        try {
            $orderCapture->save();

            $debug_str = 'markCapturedPayment / after orderCapture->save';
            DebugLog::msg($debug_str, 'notice');

            if ($this->tools->getIsset('submitState')) {
                $debug_str = 'markCapturedPayment / getIsset returned true';
                DebugLog::msg($debug_str, 'notice');

                return true;
            }

            $completedState = $this->configuration->get(Config::PAYMENT_COMPLETED);

            $debug_str = 'markCapturedPayment / order current state:' . $order->current_state . ' compared with: ' . $completedState;
            DebugLog::msg($debug_str, 'notice');

            if ((int) $order->current_state !== (int) $completedState) {
                $totalCaptured = $this->tools->displayNumber($orderCapture->getTotalCaptured());
                $totalPaid = $this->tools->displayNumber($order->total_paid_tax_incl);

                $debug_str = 'markCapturedPayment / total captured:' . $totalCaptured . ' total paid: ' . $totalPaid;
                DebugLog::msg($debug_str, 'notice');

                if ($totalCaptured === $totalPaid) {
                    $order->setCurrentState($completedState);
                }
            }

            return true;
        } catch (\Exception $exception) {
            $debug_str = 'markCapturedPayment / exception error:' . $exception->getMessage();
            DebugLog::msg($debug_str, 'error');

            $logger = $this->loggerFactory->create();
            $logger->warning(
                'order with reference ' . $order->reference . ' thrown exception ' . $exception->getMessage(),
                [
                    'trace' => $exception->getTraceAsString(),
                ]
            );

            return false;
        }
    }
}
