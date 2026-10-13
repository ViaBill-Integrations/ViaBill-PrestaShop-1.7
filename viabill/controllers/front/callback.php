<?php
/** NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

use ViaBill\Util\DebugLog;

/**
 * ViaBill CallBack Module Front Controller Class.
 *
 * Class ViaBillCallBackModuleFrontController
 */
class ViaBillCallBackModuleFrontController extends ModuleFrontController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    /**
     * Validate, Serialize And Log Respond From ViaBill Payment.
     * Changing Order Status By CallBack.
     *
     * @throws Exception
     */
    public function postProcess()
    {
        /**
         * @var \ViaBill\Factory\RequestFactory $requestFactory
         */
        $requestFactory = $this->module->getModuleContainer()->get('factory.request');
        $request = $requestFactory->create();

        /**
         * @var \ViaBill\Factory\SerializerFactory $serializerFactory
         */
        $serializerFactory = $this->module->getModuleContainer()->get('factory.serializer');

        /** @var \ViaBill\Factory\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getModuleContainer()->get('factory.logger');
        $logger = $loggerFactory->create();

        $serializer = $serializerFactory->getSerializer();

        $requestContent = '';
        $callBackResponse = null;

        try {
            // Capture Content-Type to help detection
            $ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : '');

            // 1) Try Symfony's raw content first
            $requestContent = $request->getContent();
            if ($requestContent === null) {
                $requestContent = '';
            }

            // 2) Fallback to php://input
            if ($requestContent === '') {
                $rawInput = @file_get_contents('php://input');
                if (is_string($rawInput) && $rawInput !== '') {
                    $requestContent = $rawInput;
                }
            }

            // 3) Normalize to JSON
            $normalizedJson = '';
            if ($requestContent !== '') {
                $trim = ltrim($requestContent);
                $looksJsonHeader = is_string($ct) && stripos($ct, 'application/json') !== false;
                $looksJsonBody   = $trim !== '' && ($trim[0] === '{' || $trim[0] === '[');

                if ($looksJsonHeader || $looksJsonBody) {
                    // Raw body is JSON already
                    $normalizedJson = $requestContent;
                } else {
                    // Raw body might be form-encoded query string; convert it
                    if (strpos($requestContent, '=') !== false) {
                        $asArray = [];
                        parse_str($requestContent, $asArray);
                        if (!empty($asArray)) {
                            $normalizedJson = json_encode($asArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
                        }
                    }
                }
            }

            // 4) Fallback to superglobals if we still don't have JSON
            if ($normalizedJson === '') {
                if (!empty($_POST)) {
                    $normalizedJson = json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
                } elseif (!empty($_GET)) {
                    $normalizedJson = json_encode($_GET, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
                }
            }

            // 5) As a last resort, log & short-circuit to avoid deserializing an empty string
            if ($normalizedJson === '' || $normalizedJson === false) {
                DebugLog::msg('Callback requestContent empty after normalization. ' .
                    'CT=' . DebugLog::export($ct) .
                    ' $_POST=' . DebugLog::export($_POST) .
                    ' $_GET='  . DebugLog::export($_GET)
                );
                http_response_code(400);
                $this->ajaxDie('ERROR');
            }

            // 6) Deserialize using normalized JSON
            /** @var \ViaBill\Object\Api\CallBack\CallBackResponse $callBackResponse */
            $callBackResponse = $serializer->deserialize(
                $normalizedJson,
                'ViaBill\Object\Api\CallBack\CallBackResponse',
                'json'
            );

            // add a log entry
            $debug_str = DebugLog::export($requestContent);
            DebugLog::msg('Callback postProcess / content success: ' . $debug_str);
        } catch (Exception $exception) {
            $logger->error(
                'Callback parse exception',
                [
                    'exception' => $exception->getMessage(),
                    'content' => $requestContent,
                ]
            );            

            $er = $exception->getMessage();
            $exc_msg = DebugLog::export($er);
            $debug_str = DebugLog::export($requestContent);
            DebugLog::msg('Callback postProcess / [error msg: '.$exc_msg.'][content: '.$debug_str.']');

            http_response_code(400);
            $this->ajaxDie('ERROR');
        }        

        /**
         * @var \ViaBill\Service\Validator\CallBack\OrderCallBackValidator $orderValidator
         */
        $orderValidator = $this->module->getModuleContainer()->get('service.validator.order.callBack');

        if (!$orderValidator->validate($callBackResponse)) {
            DebugLog::msg('Callback REJECTED with HTTP 400: validation failed, the order and the transaction history are not changed (see the validation entries above).', 'error');

            // Invalid signature, URL key, order, reference, amount or currency:
            // the request itself is wrong, so answer 400.
            http_response_code(400);
            $this->ajaxDie('NOT VALID ORDER');
        }

        DebugLog::msg('Callback ACCEPTED: validation succeeded, updating the transaction history and the order status.');

        // Update the transaction history only once the callback has been
        // authenticated, so unauthenticated requests cannot alter it.
        $idTransaction = $callBackResponse->getTransaction();
        if ($idTransaction) {
            $idTransactionHistory = \ViaBillTransactionHistory::getPrimaryKeyByTransaction($idTransaction);
            if ($idTransactionHistory) {
                $transactionHistory = new \ViaBillTransactionHistory($idTransactionHistory);
                $transactionHistory->updateAfterCallback($callBackResponse);
            }
        }

        /**
         * @var \ViaBill\Service\Order\OrderStatusService $orderStatusService
         */
        $orderStatusService = $this->module->getModuleContainer()->get('service.order.orderStatus');
        $orderStatusService->changeOrderStatusByCallBack($callBackResponse);

        DebugLog::msg('Callback FINISHED [order: ' . (int) $callBackResponse->getOrderNumber() . ']');
        $this->ajaxDie('FINISHED');
    }
}
