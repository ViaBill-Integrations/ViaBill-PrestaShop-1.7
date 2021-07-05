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

use ViaBill\Util\DebugLog;

/**
 * ViaBill Checkout Module Front Controller Class.
 *
 * Class ViaBillCheckoutModuleFrontController
 */
class ViaBillCheckoutModuleFrontController extends ModuleFrontController
{
    /**
     * Filename Constant
     */
    const FILENAME = 'checkout';

    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    /**
     * Validating Payment.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function checkAccess()
    {
        /**
         * @var \ViaBill\Service\Validator\Payment\PaymentValidator $validator
         */
        $validator = $this->module->getModuleContainer()->get('service.validator.payment');

        return parent::checkAccess() &&
            $validator->validate(
                $this->context->link,
                $this->context->cart,
                $this->context->customer
            );
    }

    /**
     * Send Payment Request And Checks For Errors.
     * Redirects To Effective Url From ViaBill Respond.
     *
     * @throws Exception
     */
    public function postProcess()
    {
        $order = $this->getOrder();

        // Debug info
        $order_str = (empty($order))?'empty':var_export($order, true);
        $debug_str = "[order: {$order_str}]";
        DebugLog::msg("Checkout postProcess / ".$debug_str);

        /**
         * @var \ViaBill\Util\LinksGenerator $linkGenerator
         */
        $linkGenerator = $this->module->getModuleContainer()->get('util.linkGenerator');

        /**
         * @var \ViaBill\Service\Api\Payment\PaymentService $paymentService
         */
        $paymentService = $this->module->getModuleContainer()->get('service.api.payment');

        $paymentRequest = $this->createPaymentRequest($order, $linkGenerator);

        // Debug info
        $request_str = (empty($paymentRequest))?'empty':var_export($paymentRequest, true);
        $debug_str = "[payment request: {$request_str}]";
        DebugLog::msg("Checkout postProcess / ".$debug_str);

        $errorMessage =
            $this->module->l('An unexpected error occurred while processing the payment.', self::FILENAME);

        try {
            $paymentResponse = $paymentService->createPayment($paymentRequest);
            $errors = $paymentResponse->getErrors();
            if (!empty($errors)) {
                $this->errors[] = $errorMessage;

                // Debug info
                $debug_str = var_export($errors, true);
                DebugLog::msg("Checkout postProcess / errors: ".$debug_str);
            } else {
                // Debug info
                $debug_str = $paymentResponse->getEffectiveUrl();
                DebugLog::msg("Checkout postProcess / success: ".$debug_str);

                Tools::redirect($paymentResponse->getEffectiveUrl());
            }
        } catch (Exception $exception) {
            /**
             * @var \ViaBill\Factory\LoggerFactory $loggerFactory
             */
            $loggerFactory = $this->module->getModuleContainer()->get('factory.logger');
            $logger = $loggerFactory->create();

            // Debug info
            $debug_str = $exception->getMessage();
            DebugLog::msg("Checkout postProcess / exception ".$debug_str);

            $logger->error(
                'Exception in checkout process',
                array(
                    'exception' => $exception->getMessage()
                )
            );
            $this->errors[] = $errorMessage;
        }

        $order->setCurrentState(
            (int) Configuration::get(\ViaBill\Config\Config::PAYMENT_ERROR)
        );

        $this->redirectWithNotifications(
            $linkGenerator->getOrderConfirmationLink(
                $this->context->link,
                $order
            )
        );
    }

    /**
     * Gets Order By Id Or By Cart.
     *
     * @return Order
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getOrder()
    {
        if (Tools::isSubmit('id_order')) {
            return new Order(Tools::getValue('id_order'));
        }

        /**
         * @var \ViaBill\Service\Order\CreateOrderService $orderCreateService
         */
        $orderCreateService = $this->module->getModuleContainer()->get('service.order.createOrder');
        $order =  $orderCreateService->createOrder(
            $this->context->cart,
            $this->context->currency
        );

        /**
         * @var \ViaBill\Service\Cart\MemorizeCartService $memorizeService
         */
        $memorizeService = $this->module->getModuleContainer()->get('cart.memorizeCartService');
        $memorizeService->memorizeCart($order);

        return $order;
    }

    /**
     * Creates Payment Request.
     *
     * @param Order $order
     * @param \ViaBill\Util\LinksGenerator $linksGenerator
     *
     * @return \ViaBill\Object\Api\Payment\PaymentRequest
     *
     * @throws Exception
     */
    private function createPaymentRequest(Order $order, \ViaBill\Util\LinksGenerator $linksGenerator)
    {
        /**
         * @var \ViaBill\Service\UserService $userService
         */
        $userService = $this->module->getModuleContainer()->get('service.user');
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');
        /**
         * @var \ViaBill\Util\SignaturesGenerator $signatureGenerator
         */
        $signatureGenerator = $this->module->getModuleContainer()->get('util.signatureGenerator');

        $user = $userService->getUser();

        $currency = new Currency($order->id_currency);

        $callBackUrl = $this->getCallBackUrl(
            array(
                'key' => $signatureGenerator->generateCallBackSecurityKey()
            )
        );

        $totalAmount = $order->total_paid_tax_incl;
        $currencyIso = $currency->iso_code;
        $transaction = $order->reference;
        $idOrder = $order->id;

        $successUrl = $this->getReturnUrl([
            "id_order" => $order->id,
        ]);

        $cancelUrl = $linksGenerator->getCancelLink(
            $this->context->link,
            $order
        );

        $md5Check = $signatureGenerator->generatePaymentCheckSum(
            $user,
            $totalAmount,
            $currencyIso,
            $transaction,
            $idOrder,
            $successUrl,
            $cancelUrl
        );

        $customerInfo = $this->getCustomerInfo($order);

        return new \ViaBill\Object\Api\Payment\PaymentRequest(
            $user->getKey(),
            $transaction,
            $idOrder,
            $totalAmount,
            $currency->iso_code,
            $successUrl,
            $cancelUrl,
            $callBackUrl,
            $config->isTestingEnvironment(),
            $md5Check,
            $customerInfo
        );
    }

    /**
     * Gets CallBack Url
     *
     * @param array $params
     *
     * @return string
     */
    private function getCallBackUrl($params = array())
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'callback',
            $params
        );
    }

    /**
     * Gets Return Url
     *
     * @param array $params
     *
     * @return string
     */
    private function getReturnUrl($params = array())
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'return',
            $params
        );
    }

    /**
     * Get information about the customer for the active order
     * 
     * @param Order $order
     * 
     * @return array
     */
    private function getCustomerInfo(Order $order) {        
        $info = array(
          'email'=>'',
          'phone'=>'',
          'first_name'=>'',
          'last_name'=>'',
          'full_name'=>'',
          'address'=>'',
          'city'=>'',
          'postal_code'=>'',
          'country'=>''
        );

        // sanity check
        if (empty($order)) return $info;

        $id_address_delivery = (int) $order->id_address_delivery;
        if (!empty($id_address_delivery)) {
            $details = new Address($id_address_delivery);

            if (property_exists($details, 'email')) {
                $info['email'] = $details->email;
            }
            $phone = '';
            if (property_exists($details, 'phone_mobile')) {
                $phone = trim($details->phone_mobile);
            } 
            if (empty($phone)) {
                if (property_exists($details, 'phone')) {
                    $phone = trim($details->phone);
                }
            }                        
            $info['phone'] = $phone;
            if (property_exists($details, 'firstname')) {
                $info['first_name'] = $details->firstname;
            }
            if (property_exists($details, 'lastname')) {
                $info['last_name'] = $details->lastname;
            }
            $info['full_name'] = trim($info['first_name'].' '.$info['last_name']);
            $address = '';
            if (property_exists($details, 'address1')) {
                $address .= $details->address1;
            }
            if (property_exists($details, 'address2')) {
                $address .= ' '.$details->address2;
            }
            $info['address'] = trim($address);
            if (property_exists($details, 'city')) {
                $info['city'] = $details->city;
            }
            if (property_exists($details, 'postcode')) {
                $info['postal_code'] = $details->postcode;
            }
            if (property_exists($details, 'country')) {
                $info['country'] = $details->country;
            }            
           
            if (!empty($details->id_customer)) {
                $customer = new Customer((int)($details->id_customer));
                if (property_exists($customer, 'email')) {
                    $info['email'] = $customer->email;
                }
            }            
        }
          
        return $info;
    }
}
