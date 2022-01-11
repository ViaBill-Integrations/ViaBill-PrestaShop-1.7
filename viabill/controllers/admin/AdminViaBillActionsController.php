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

use ViaBill\Util\NumberUtility;

/**
 * ViaBill Actions Controller Class.
 *
 * Class AdminViaBillActionsController
 */
class AdminViaBillActionsController extends ModuleAdminController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    /**
     * Calls Class Processes By Checking Is Ajax Is False.
     *
     * @return bool|ObjectModel|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (!$this->ajax) {
            $this->capturePostProcess();
        }

        $this->captureAjaxConfirmationMessageProcess();
    }

    /**
     * Capture Order Action And Handle It.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function capturePostProcess()
    {
        /** @var \ViaBill\Service\Handler\PaymentManagementHandler $paymentHandler */
        /** @var \ViaBill\Service\MessageService $messageService */
        $paymentHandler = $this->module->getModuleContainer()->get('service.handler.paymentManagement');
        $messageService = $this->module->getModuleContainer()->get('service.message');

        $order = new Order(Tools::getValue('id_order'));

        $isCapture = Tools::isSubmit('capturePayment');
        $isRefund = Tools::isSubmit('refundPayment');
        $isCancel = Tools::isSubmit('cancelPayment');
        $isRenew = Tools::isSubmit('renewPayment');

        $amount = 0.0;

        if ($isCapture) {
            $amount = (float) NumberUtility::replaceCommaToDot(Tools::getValue('capture_amount'));
        }

        if ($isRefund) {
            $amount = (float) NumberUtility::replaceCommaToDot(Tools::getValue('refund_amount'));
        }

        $handleResponse = $paymentHandler->handle(
            $order,
            $isCancel,
            $isCapture,
            $isRefund,
            $isRenew,
            $amount
        );

        $errors = $handleResponse->getErrors();
        $warnings = $handleResponse->getWarnings();
        $confirmations = [];

        if (empty($errors) && $handleResponse->getSuccessMessage()) {
            $confirmations[] = $handleResponse->getSuccessMessage();
        }

        $messageService->redirectWithMessages($order, $confirmations, $errors, $warnings);
    }

    /**
     * Capture Order Action Ajax Confirmation And Return Confirmation Message.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function captureAjaxConfirmationMessageProcess()
    {
        if ($this->token !== Tools::getValue('token')) {
            $this->ajaxDie($this->l('Form token mismatch detected.'));
        }

        $action = Tools::getValue('action');

        if ($action !== 'displayMessage') {
            return;
        }

        $order = new Order((int) Tools::getValue('idOrder'));
        $amount = (float) Tools::getValue('amount');
        $type = Tools::getValue('type');

        if (!Validate::isLoadedObject($order)) {
            $this->ajaxDie($this->l('Form confirmation error. Order was not found.'));
        }

        $message = '';
        $currency = new Currency($order->id_currency);
        $convertedPrice = Tools::displayPrice($amount, $currency);

        switch ($type) {
            case 'capture':
                $message =
                     sprintf(
                         $this->l('Are you sure that you want to capture %s ?'),
                         $convertedPrice
                     );
                break;
            case 'refund':
                $message =
                    sprintf(
                        $this->l('Are you sure that you want to refund %s ?'),
                        $convertedPrice
                    );
                break;
        }

        $this->ajaxDie($message);
    }
}
