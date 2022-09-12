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
        $order_str = (empty($order)) ? 'empty' : var_export($order, true);
        $debug_str = "[order: {$order_str}]";
        DebugLog::msg('Checkout postProcess / ' . $debug_str);

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
        $request_str = (empty($paymentRequest)) ? 'empty' : var_export($paymentRequest, true);
        $debug_str = "[payment request: {$request_str}]";
        DebugLog::msg('Checkout postProcess / ' . $debug_str);

        $errorMessage =
            $this->module->l('An unexpected error occurred while processing the payment.', self::FILENAME);

        try {
            $paymentResponse = $paymentService->createPayment($paymentRequest);
            $errors = $paymentResponse->getErrors();
            if (!empty($errors)) {
                $this->errors[] = $errorMessage;

                // Debug info
                $debug_str = var_export($errors, true);
                DebugLog::msg('Checkout postProcess / errors: ' . $debug_str);
            } else {
                // Debug info
                $debug_str = $paymentResponse->getEffectiveUrl();
                DebugLog::msg('Checkout postProcess / success: ' . $debug_str);

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
            DebugLog::msg('Checkout postProcess / exception ' . $debug_str);

            $logger->error(
                'Exception in checkout process',
                [
                    'exception' => $exception->getMessage(),
                ]
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
        $order = $orderCreateService->createOrder(
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
    private function createPaymentRequest(Order $order, ViaBill\Util\LinksGenerator $linksGenerator)
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
            [
                'key' => $signatureGenerator->generateCallBackSecurityKey(),
            ]
        );

        $totalAmount = $order->total_paid_tax_incl;
        $currencyIso = $currency->iso_code;
        $transaction = $order->reference;
        $idOrder = $order->id;

        $successUrl = $this->getReturnUrl([
            'id_order' => $order->id,
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

        $cartInfo = $this->getCartInfo($order);

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
            $customerInfo,
            $cartInfo
        );
    }

    /**
     * Gets CallBack Url
     *
     * @param array $params
     *
     * @return string
     */
    private function getCallBackUrl($params = [])
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
    private function getReturnUrl($params = [])
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
    private function getCustomerInfo(Order $order)
    {
        $info = [
            'email'=>'',
            'phoneNumber'=>'',
            'firstName'=>'',
            'lastName'=>'',
            'fullName'=>'',
            'address'=>'',
            'city'=>'',
            'postalCode'=>'',
            'country'=>''
        ];

        // sanity check
        if (empty($order)) {
            return $info;
        }

        $id_address_delivery = (int) $order->id_address_delivery;
        if (!empty($id_address_delivery)) {
            $details = new Address($id_address_delivery);

            $country_code = null;
            if (property_exists($details, 'id_country')) {         
                $country_obj = new Country($details->id_country);
                if (property_exists($country_obj, 'iso_code')) {
                    $country_code = strtoupper($country_obj->iso_code);
                }
            } else if (property_exists($details, 'country')) {
                $country_code = $details->country;                
            }

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
            $info['phoneNumber'] = $this->sanitizePhone($phone, $country_code);
            if (property_exists($details, 'firstname')) {
                $info['firstName'] = $details->firstname;
            }
            if (property_exists($details, 'lastname')) {
                $info['lastName'] = $details->lastname;
            }
            $info['fullName'] = trim($info['firstName'] . ' ' . $info['lastName']);
            $address = '';
            if (property_exists($details, 'address1')) {
                $address .= $details->address1;
            }
            if (property_exists($details, 'address2')) {
                $address .= ' ' . $details->address2;
            }
            $info['address'] = trim($address);
            if (property_exists($details, 'city')) {
                $info['city'] = $details->city;
            }
            if (property_exists($details, 'postcode')) {
                $info['postalCode'] = $details->postcode;
            }
            if (property_exists($details, 'country')) {
                $info['country'] = $country_code;
            }

            if (!empty($details->id_customer)) {
                $customer = new Customer((int) ($details->id_customer));
                if (property_exists($customer, 'email')) {
                    $info['email'] = $customer->email;
                }
            }
        }

        return $info;
    }

    /**
     * Get information about the cart items/products for the active order
     *
     * @param Order $order
     *
     * @return array
     */
    private function getCartInfo(Order $order)
    {        
        // sanity check
        if (empty($order)) {
            return null;
        }

        $products = $order->getProducts();
        if (empty($products)) {
            return null;
        }

        $lang_id = (int)$this->context->language->id;
        
        $tax_total = (float) ($order->getTotalProductsWithoutTaxes() - $order->getTotalProductsWithTaxes());
        if ($tax_total > 0.01) {
            $tax_total_amount = number_format($tax_total, 2);
        } else {
            $tax_total_amount = '0';
        }

        $currency_name = '';
        $currency_id = (int) $order->id_currency;
        if ($currency_id) {
            $currency = new Currency($currency_id, $lang_id);
            if ($currency) {
                $currency_name = $currency->iso_code;
            }            
        }        
        
        $order_quantity = 0;

        $order_data = $order->getFields();

        $billing_email = '';
        $billing_phone = '';
        $shipping_city = '';
        $shipping_postcode = '';
        $shipping_country = '';        

        if (isset($order_data['id_address_invoice'])) {
            if (!empty($order_data['id_address_invoice'])) {
                $billing_address_obj = new Address((int) $order_data['id_address_invoice']);
                $billing_address = $billing_address_obj->getFields();
                $billing_street = trim($billing_address['address1'].' '.$billing_address['address2']);
                $billing_city = trim($billing_address['city']);
                $billing_postcode = trim($billing_address['postcode']);
                $billing_country = trim($billing_address['country']);
                $billing_phone = (empty($billing_address['phone_mobile']))?$billing_address['phone']:$billing_address['phone_mobile'];
            }
        }

        if (isset($order_data['id_address_delivery'])) {
            if (!empty($order_data['id_address_delivery'])) {
                $shipping_address_obj = new Address((int) $order_data['id_address_delivery']);
                $shipping_address = $shipping_address_obj->getFields();
                $shipping_street = trim($shipping_address['address1'].' '.$shipping_address['address2']);
                $shipping_city = trim($shipping_address['city']);
                $shipping_postcode = trim($shipping_address['postcode']);
                $shipping_country = trim($shipping_address['country']);
            }
        }

        $shipping_same_as_billing = 'yes';
        $compare_addresses = [
            'street' => [$billing_address, $shipping_address],
            'city' => [$billing_city, $shipping_city],
            'postcode' => [$billing_postcode, $shipping_postcode],
            'country' => [$billing_country, $shipping_country]
        ];
        foreach ($compare_addresses as $c_values) {
            $b_value = $c_values[0];
            $s_value = $c_values[1];
            if (!empty($b_value) && !empty($s_value)) {
                if ($b_value != $s_value) {
                    $shipping_same_as_billing = 'no';
                }
            }
        }

        if (!empty($order_data['id_customer'])) {
            $customer = new Customer((int) ($order_data['id_customer']));
            if (property_exists($customer, 'email')) {
                $billing_email = $customer->email;
            }
        }                 

        $info = [           
            'date_created' => $order_data['date_add'],
            'subtotal'=> number_format((float) $order->total_products, 2),        
            'tax' => number_format((float) $tax_total_amount, 2),
            'shipping'=> number_format((float) $order->total_shipping, 2),
            'discount'=> number_format((float) $order->total_discounts, 2),
            'total'=> number_format((float) $order->total_paid, 2),
            'currency'=>$currency_name,
            'quantity'=> $order_quantity,
            'billing_email' => $billing_email,
            'billing_phone' => $billing_phone, 
            'shipping_city' => $shipping_city,
            'shipping_postcode' => $shipping_postcode,
            'shipping_country' => $shipping_country,
            'shipping_same_as_billing' => $shipping_same_as_billing,
            'products' => []
        ];      

        foreach ( $products as $product ) {
            $product_id = (int) $product['id_product'];                       
            
            $total_tax_incl = (float) $product['total_price_tax_incl']; // or total_wt
            $total_tax_excl = (float) $product['total_price_tax_excl']; // or total_price
            $tax_amount = abs($total_tax_incl - $total_tax_excl);
            $tax_percentage = ($tax_amount/$total_tax_excl)*100.00;
            $product_quantity = (int) $product['product_quantity'];
            $order_quantity += $product_quantity;
            
            $product_entry = [
                'name' => $product['product_name'],
                'quantity' => $product_quantity,
                'subtotal' => number_format($total_tax_excl, 2),
                'tax' => number_format($tax_amount, 2)
            ];

            if ($tax_amount > 0.01) {
                $product_entry['tax_class'] = number_format($tax_percentage, 2);
            }

            $product_url = null;
            $image_url = null;
            
            if (!empty($lang_id)) {       
                $product_id = (int) $product['id_product']; 
                if ($product_id) {
                    $product_obj = new Product($product_id, $lang_id);
                    if (!empty($product_obj)) {     
                        // Initialize the link object
                        // $link = new Link;
                        $product_url = $this->context->link->getProductLink($product_id);
                        $image = $product_obj->getCover($product_id);
                        if (!empty($image)) {
                            $image_url = $this->context->link->getImageLink($lang_id, $image['id_image'], 'home_default');
                        }
                        /*                        
                        $description = $product_obj->description[$lang_id];
                        $short_description = $product_obj->description_short[$lang_id];
                        if (!empty($short_description)) {
                            $product_entry['description'] = $this->truncateDescription(strip_tags($short_description));
                        } else if (!empty($description)) {
                            $product_entry['description'] = $this->truncateDescription(strip_tags($description));
                        }
                        */
                        
                        $meta_description = $product_obj->meta_description[$lang_id];
                        $meta_keywords = $product_obj->meta_keywords[$lang_id];
                        if (!empty($meta_description)) {                            
                            $product_entry['meta'] = $this->truncateDescription(strip_tags($meta_description[$lang_id]));
                        } else if (!empty($meta_keywords)) {
                            $product_entry['meta'] = $this->truncateDescription(strip_tags($meta_keywords[$lang_id]));
                        }                        
                    }
                }

                $category_id = (int) $product['id_category_default'];
                if ($category_id) {
                    $category = new Category($category_id, $lang_id);
                    if (!empty($category)) {
                        $product_entry['categories'] = $category->name;
                    }
                }

                $manufacturer_id = (int) $product['id_manufacturer'];
                if ($manufacturer_id) {
                    $manufacturer = new Manufacturer($manufacturer_id, $lang_id);
                    if (!empty($manufacturer)) {
                        $product_entry['manufacturer'] = $manufacturer->name;
                    }
                }
            }                        

            $weight = (float)$product['weight'];
            if ($weight > 0.01) {
                $product_entry['weight'] = number_format($weight, 2);
            }
            if (!empty($product['download_hash'])) {
                $product_entry['virtual'] = 1;
            }
            if (!empty($product['on_sale'])) {
                $product_entry['on_sale'] = 1;
            }               
                        
            $reduction_amount = (float) $product['reduction_amount']."\n";
            $reduction_percent = (float) $product['reduction_percent']."\n";
            $initial_price = (float) $product['price'];                
            $product_discount = 0;
            if ($reduction_amount > 0.01) {                
                $product_discount = $reduction_amount;
            } else if ($reduction_percent > 0.01) {      
                $product_discount = ($reduction_percent * $initial_price)/100;                          
            } 
            if ($product_discount) {
                $product_entry['discount'] = number_format($product_discount * $product_quantity, 2);
            }

            if (!empty($product_url)) {
                $product_entry['product_url'] = str_replace('\\/','/', $product_url);
            }
            if (!empty($image_url)) {
                $product_entry['image_url'] = str_replace('\\/','/', $image_url);
            }

            $info['products'][] = $product_entry;
        }

        // update order quantity with the calculated value
        $info['quantity'] = $order_quantity;
               
        return $info;        
    }    

    public function sanitizePhone($phone, $country_code = null) {
        if (empty($phone)) {
            return $phone;
        }
        if (empty($country_code)) {
            return $phone;
        }
        $clean_phone = str_replace(array('+','(',')','-',' '),'',$phone);
        if (strlen($clean_phone)<3) {
            return $phone;
        }
        $country_code = strtoupper($country_code);
        switch ($country_code) {
            case 'US':
            case 'USA': // +1
                $prefix = substr($clean_phone, 0, 1);
                if ($prefix == '1') {
                    $phone_number = substr($clean_phone, 1);
                    if (strlen($phone_number)==10) {
                        $phone = $phone_number;
                    }
                }                
                break;
            case 'DK': 
            case 'DNK': // +45
                $prefix = substr($clean_phone, 0, 2);
                if ($prefix == '45') {
                    $phone_number = substr($clean_phone, 2);
                    if (strlen($phone_number)==8) {
                        $phone = $phone_number;
                    }
                }
                break;
            case 'ES': 
            case 'ESP': // +34
                $prefix = substr($clean_phone, 0, 2);
                if ($prefix == '34') {
                    $phone_number = substr($clean_phone, 2);
                    if (strlen($phone_number)==9) {
                        $phone = $phone_number;
                    }
                }
                break;        
        }
  
        return $phone;
    }
    
    public function truncateDescription($text, $maxchar=200, $end='...') {
        if (strlen($text) > $maxchar || $text == '') {
            $words = preg_split('/\s/', $text);      
            $output = '';
            $i      = 0;
            while (1) {
                $length = strlen($output)+strlen($words[$i]);
                if ($length > $maxchar) {
                    break;
                } 
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        } 
        else {
            $output = $text;
        }
        return $output;
    }

}
