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
            $requestContent = $request->getContent();

            /**
             * @var \ViaBill\Object\Api\CallBack\CallBackResponse $callBackResponse
             */
            $callBackResponse = $serializer->deserialize(
                $requestContent,
                'ViaBill\Object\Api\CallBack\CallBackResponse',
                'json'
            );

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
            DebugLog::msg('Callback postProcess / [error msg: ' . $exc_msg . '][content: ' . $debug_str . ']');

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
