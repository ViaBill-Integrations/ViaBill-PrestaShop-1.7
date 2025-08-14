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
                    'CT=' . var_export($ct, true) .
                    ' $_POST=' . var_export($_POST, true) .
                    ' $_GET='  . var_export($_GET, true)
                );
                $this->ajaxDie('ERROR'); // or keep your current behavior
            }

            // 6) Deserialize using normalized JSON
            /** @var \ViaBill\Object\Api\CallBack\CallBackResponse $callBackResponse */
            $callBackResponse = $serializer->deserialize(
                $normalizedJson,
                'ViaBill\Object\Api\CallBack\CallBackResponse',
                'json'
            );            

            // update transaction history
            $idTransaction = $callBackResponse->getTransaction();            
            if ($idTransaction) {
                $idTransactionHistory = \ViaBillTransactionHistory::getPrimaryKeyByTransaction($idTransaction);                
                if ($idTransactionHistory) {
                    $transactionHistory = new \ViaBillTransactionHistory($idTransactionHistory);                    
                    $transactionHistory->updateAfterCallback($callBackResponse);
                }
            }                        

            // add a log entry
            $debug_str = var_export($requestContent, true);
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
            $exc_msg = var_export($er, true);
            $debug_str = var_export($requestContent, true);
            $raw_request = print_r($_REQUEST, true);
            DebugLog::msg('Callback postProcess / [error msg: '.$exc_msg.'][content: '.$debug_str.'][raw request: '.$raw_request.']');

            $this->ajaxDie('ERROR');
        }        

        /**
         * @var \ViaBill\Service\Validator\CallBack\OrderCallBackValidator $orderValidator
         */
        $orderValidator = $this->module->getModuleContainer()->get('service.validator.order.callBack');

        if (!$orderValidator->validate($callBackResponse)) {
            $this->ajaxDie('NOT VALID ORDER');
        }

        /**
         * @var \ViaBill\Service\Order\OrderStatusService $orderStatusService
         */
        $orderStatusService = $this->module->getModuleContainer()->get('service.order.orderStatus');
        $orderStatusService->changeOrderStatusByCallBack($callBackResponse);

        $this->ajaxDie('FINISHED');
    }
}
