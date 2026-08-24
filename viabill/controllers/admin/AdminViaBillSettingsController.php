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

use ViaBill\Config\Config;
use ViaBill\Controller\AbstractAdminController as ModuleAdminController;
use ViaBill\Util\DebugLog;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * ViaBill Settings Controller Class.
 *
 * Class AdminViaBillSettingsController
 */
class AdminViaBillSettingsController extends ModuleAdminController
{
    /**
     * Contact email address
     */
    const VIABILL_TECH_SUPPORT_EMAIL = 'tech@viabill.com';

    /**
     * AdminViaBillSettingsController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();

        $this->override_folder = 'field-option-settings/';
        $this->tpl_folder = 'field-option-settings/';
    }

    /**
     * Sets Success Messages To Cookie And Unset Them After Print.
     */
    public function init()
    {
        if (isset($this->context->cookie->authSuccessMessage)) {
            $this->confirmations[] = $this->context->cookie->authSuccessMessage;
            unset($this->context->cookie->authSuccessMessage);
        }

        if (isset($this->context->cookie->saveSuccessMessage)) {
            $this->confirmations[] = $this->context->cookie->saveSuccessMessage;
            unset($this->context->cookie->saveSuccessMessage);
        }

        parent::init();

        $this->initOptionsCustomVariables();
        $this->initOptions();
    }

    /**
     * Adding Warning When All PriceTag Settings Are Off.
     * Adding Successful Settings Update Message.
     *
     * @return bool|ObjectModel|void
     */
    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitOptions' . $this->table)) {
            $orderStatusMultiselect = Tools::getIsset('order_status_multiselect') ?
                Tools::getValue('order_status_multiselect') :
                [];

            /** @var \ViaBill\Service\Order\OrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getModuleContainer()->get('service.order.orderStatus');
            $orderStatusService->setEncodedCaptureMultiselectOrderStatuses($orderStatusMultiselect);

            $this->context->cookie->saveSuccessMessage = $this->l('The settings have been successfully updated.');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminViaBillSettings'));
        }
    }

    /**
     * Adds CSS And JS Files To ViaBill Settings Controller.
     *
     * @param bool $isNewTheme
     *
     * @throws Exception
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        if ($config->isLoggedIn()) {
            $this->addJS($this->module->getLocalPath() . '/views/js/admin/settings.js');
            $this->addCSS($this->module->getLocalPath() . '/views/css/admin/settings.css');
            $this->addCSS($this->module->getLocalPath() . '/views/css/admin/info-block.css');
        }
    }

    /**
     * Init ViaBill Settings Controller Options Variables.
     */
    private function initOptionsCustomVariables()
    {
        /** @var \ViaBill\Service\Order\OrderStatusService $orderStatusService */
        $orderStatusService = $this->module->getModuleContainer()->get('service.order.orderStatus');

        $this->context->smarty->assign(
            [
                'multiselectOrderStatuses' => $orderStatusService->getOrderStatusesForMultiselect(),
            ]
        );
    }

    /**
     * Init ViaBill Settings Controller Options.
     */
    private function initOptions()
    {
        $pricetagSettingsInfoBlockText =
            $this->l('Enable ViaBill’s PriceTags to obtain the best possible conversion, and inform your customers about ViaBill.');

        $myViaBillInfoBlockText =
            $this->l('MyViaBill is where you find your settlement documents for your ViaBill transactions and upload your KYC documents.');

        $myViaBillUrl = $this->getMyViaBillLink();

        $myViaBillButtonClasses = 'btn btn-default pull-right js-go-to-viabill';
        if (!$myViaBillUrl) {
            $myViaBillButtonClasses .= ' disabled';
        }

        $orderStatusMultiselectClasses = 'order-status-multiselect js-order-status-multiselect';
        if (!Configuration::get(Config::ENABLE_AUTO_PAYMENT_CAPTURE)) {
            $orderStatusMultiselectClasses .= ' hidden-form-group';
        }

        $moduleInfoBlockText = $this->getDebugInfo();

        $this->fields_options = [];

        $moduleConflictBlockText = $this->getConflictWarning();
        if (!empty($moduleConflictBlockText)) {
            $this->fields_options[Config::SETTINGS_MODULE_CONFLICT_WARNING] = [
                'title' => $this->l('Module Conflict'),
                'icon' => 'icon-exclamation-circle',
                'fields' => [
                    Config::MODULE_CONFLICT_WARNING_BLOCK_FIELD => [
                        'type' => 'free',
                        'desc' => $moduleConflictBlockText,
                        'class' => 'hidden',
                        'form_group_class' => 'viabill-warning-block',
                    ],
                ],
            ];
        }        

        $accountInfoBlockText =
            $this->l('Enter your ViaBill API key, API secret and PriceTag script. You can find these values in your ViaBill merchant account. All three values are required for the payment gateway and the PriceTags to work.');

        $hasStoredSecret = (bool) Configuration::get(Config::API_SECRET);

        $secretDesc = $hasStoredSecret
            ? $this->l('An API secret is already saved (hidden for security). Leave this field empty to keep it.')
            : $this->l('Your ViaBill API secret.');

        $this->fields_options[Config::SETTINGS_ACCOUNT_SECTION] = [
            'title' => $this->l('ViaBill Account Credentials'),
            'icon' => 'icon-key',
            'fields' => [
                Config::ACCOUNT_INFO_BLOCK_FIELD => [
                    'type' => 'free',
                    'desc' => $this->getInfoBlockTemplate($accountInfoBlockText),
                    'class' => 'hidden',
                    'form_group_class' => 'viabill-info-block',
                ],
                Config::API_KEY => [
                    'title' => $this->l('API Key'),
                    'desc' => $this->l('Your ViaBill API key.'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                    'autocomplete' => false,
                    'required' => true,
                ],
                Config::API_SECRET => [
                    'title' => $this->l('API Secret'),
                    'desc' => $secretDesc,
                    'type' => 'password',
                    'class' => 'fixed-width-xxl',
                    'autocomplete' => false,
                ],
                Config::API_TAGS_SCRIPT => [
                    'title' => $this->l('PriceTag Script'),
                    'desc' => $this->l('Paste the whole PriceTag script snippet provided by ViaBill. You may include the opening and closing script tags; they will be removed automatically, since the module adds its own.'),
                    'type' => 'textarea',
                    'cols' => 60,
                    'rows' => 5,
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options[Config::SETTINGS_PRICETAG_SETTINGS_SECTION] = [
            'title' => $this->l('Pricetag Settings'),
            'icon' => 'icon-money',
            'fields' => [
                Config::PRICETAG_SETTINGS_INFO_BLOCK_FIELD => [
                    'type' => 'free',
                    'desc' => $this->getInfoBlockTemplate($pricetagSettingsInfoBlockText),
                    'class' => 'hidden',
                    'form_group_class' => 'viabill-info-block',
                ],

                Config::ENABLE_PRICE_TAG_ON_PRODUCT_PAGE => [
                    'title' => $this->l('Enable on Product page'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::DYNAMIC_PRICE_PRODUCT_SELECTOR => [
                    'title' => $this->l('Product price selector'),                    
                    'desc' => $this->l('The data-dynamic-price query selector'),
                    'type' => 'text',
                    'default' => Configuration::get(Config::DYNAMIC_PRICE_PRODUCT_SELECTOR),
                ],
                Config::DYNAMIC_PRICE_PRODUCT_TRIGGER => [
                    'title' => $this->l('Product price trigger'),                    
                    'desc' => $this->l('The data-dynamic-price-triggers query selector'),
                    'type' => 'text',
                    'default' => Configuration::get(Config::DYNAMIC_PRICE_PRODUCT_TRIGGER),
                ],
                           
                Config::ENABLE_PRICE_TAG_ON_CART_SUMMARY => [
                    'title' => $this->l('Enable on Cart Summary'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::DYNAMIC_PRICE_CART_SELECTOR => [
                    'title' => $this->l('Cart price selector'),
                    'desc' => $this->l('The data-dynamic-price query selector'),
                    'type' => 'text',
                    'default' => Configuration::get(Config::DYNAMIC_PRICE_CART_SELECTOR),
                ],
                Config::DYNAMIC_PRICE_CART_TRIGGER => [
                    'title' => $this->l('Cart price trigger'),
                    'desc' => $this->l('The data-dynamic-price-triggers query selector'),
                    'type' => 'text',
                    'default' => Configuration::get(Config::DYNAMIC_PRICE_CART_TRIGGER),
                ],

                Config::ENABLE_PRICE_TAG_ON_PAYMENT_SELECTION => [
                    'title' => $this->l('Enable on Payment selection'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options[Config::SETTINGS_GENERAL_CONFIGURATION_SECTION] = [
            'title' => $this->l('General Configuration'),
            'icon' => 'icon-cog',
            'fields' => [
                Config::VIABILL_TEST_MODE => [
                    'title' => $this->l('ViaBill Test Mode'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::VIABILL_LOGO_DISPLAY_IN_CHECKOUT => [
                    'title' => $this->l('Display ViaBill logo in the checkout payment step'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::SINGLE_ACTION_CAPTURE_CONF_MESSAGE => [
                    'title' => $this->l('Capture confirmation message for single action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::BULK_ACTION_CAPTURE_CONF_MESSAGE => [
                    'title' => $this->l('Capture confirmation message for bulk action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::SINGLE_ACTION_REFUND_CONF_MESSAGE => [
                    'title' => $this->l('Refund confirmation message for single action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::BULK_ACTION_REFUND_CONF_MESSAGE => [
                    'title' => $this->l('Refund confirmation message for bulk action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::SINGLE_ACTION_CANCEL_CONF_MESSAGE => [
                    'title' => $this->l('Cancel confirmation message for single action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::BULK_ACTION_CANCEL_CONF_MESSAGE => [
                    'title' => $this->l('Cancel confirmation message for bulk action'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::VIABILL_HIDE_IN_CHECKOUT => [
                    'title' => $this->l('Hide in checkout page'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options[Config::SETTINGS_PAYMENT_CAPTURE_SECTION] = [
            'title' => $this->l('Payment Capture Configuration'),
            'icon' => 'icon-money',
            'fields' => [
                Config::ENABLE_AUTO_PAYMENT_CAPTURE => [
                    'title' => $this->l('Enable ViaBill payment auto-capture'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::CAPTURE_ORDER_STATUS_MULTISELECT => [
                    'title' => $this->l('Auto-capture ViaBill payment when status is set to'),
                    'type' => 'orders_status_multiselect',
                    'class' => 'fixed-width-xxl',
                    'form_group_class' => $orderStatusMultiselectClasses,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        $this->fields_options[Config::SETTINGS_ORDER_STATES_SECTION] = [
            'title' => $this->l('Order Status Configuration'),
            'icon' => 'icon-list-alt',
            'fields' => [
                Config::ORDER_STATE_AFTER_AUTHORIZATION => [
                    'title' => $this->l('Order status after payment authorization'),
                    'desc' => $this->l('Select the order status to set when payment is authorized but not yet captured'),
                    'type' => 'select',
                    'list' => $this->getOrderStatesForSelect(),
                    'identifier' => 'id_order_state',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                ],
                Config::ORDER_STATE_AFTER_CAPTURE => [
                    'title' => $this->l('Order status after payment capture'),
                    'desc' => $this->l('Select the order status to set when payment is successfully captured'),
                    'type' => 'select',
                    'list' => $this->getOrderStatesForSelect(),
                    'identifier' => 'id_order_state',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        if (Config::isTBYBAvailable($this->context->country, null)) {
            $this->fields_options[Config::SETTINGS_TRY_BEFORE_YOU_BUY_SECTION] = [
                'title' => $this->l('Try Before You Buy'),
                'icon' => 'icon-money',
                'fields' => [
                    Config::ENABLE_TRY_BEFORE_YOU_BUY => [
                        'title' => $this->l('Enable Try Before You Buy'),
                        'validation' => 'isBool',
                        'cast' => 'boolval',
                        'type' => 'bool',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ];
        }

        $this->fields_options[Config::SETTINGS_MY_VIABILL_SECTION] = [
            'title' => $this->l('My ViaBill'),
            'icon' => 'icon-info-sign',
            'fields' => [
                Config::MY_VIABILL_INFO_BLOCK_FIELD => [
                    'type' => 'free',
                    'desc' => $this->getInfoBlockTemplate($myViaBillInfoBlockText),
                    'class' => 'hidden',
                    'form_group_class' => 'viabill-info-block',
                ],
            ],
            'buttons' => [
                [
                    'title' => $this->l('Go to MyViaBill'),
                    'icon' => 'process-icon-next',
                    'name' => 'goToMyViaBill',
                    'class' => $myViaBillButtonClasses,
                    'href' => $myViaBillUrl,
                ],
            ],
        ];

        $this->fields_options[Config::SETTINGS_DEBUG_SECTION] = [
            'title' => $this->l('Debug and troubleshooting information'),
            'icon' => 'icon-clipboard',
            'fields' => [
                Config::ENABLE_DEBUG => [
                    'title' => $this->l('Enable Debug'),
                    'validation' => 'isBool',
                    'cast' => 'boolval',
                    'type' => 'bool',
                ],
                Config::MODULE_INFO_FIELD => [
                    'type' => 'free',
                    'desc' => $this->getInfoBlockTemplate($moduleInfoBlockText),
                    'class' => 'module_info',
                    'form_group_class' => 'viabill-info-block',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    /**
     * Custom save handler for the VB_API_KEY option (called automatically
     * by AdminController::processUpdateOptions()).
     *
     * @param string $value
     */
    public function updateOptionVbApiKey($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            $this->errors[] = $this->l('The API key is required.');

            return;
        }

        Configuration::updateValue(Config::API_KEY, $value);
    }

    /**
     * Custom save handler for the VB_API_SECRET option (called automatically
     * by AdminController::processUpdateOptions()).
     *
     * The API secret field is rendered as an empty password input so the
     * secret is never printed back to the page. An empty submitted value
     * therefore means "keep the currently stored secret".
     *
     * @param string $value
     */
    public function updateOptionVbApiSecret($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            if (!Configuration::get(Config::API_SECRET)) {
                $this->errors[] = $this->l('The API secret is required.');
            }

            return;
        }

        Configuration::updateValue(Config::API_SECRET, $value);
    }

    /**
     * Custom save handler for the VB_TAGS_SCRIPT option (called automatically
     * by AdminController::processUpdateOptions()).
     *
     * A custom handler is needed because the generic option processing
     * validates values with Validate::isCleanHtml(), which would reject the
     * PriceTag script. The outer <script> tags (if pasted) are stripped,
     * because the front-office template adds its own.
     *
     * @param string $value
     */
    public function updateOptionVbTagsScript($value)
    {
        $rawScript = trim((string) $value);

        if ($rawScript === '') {
            $this->errors[] = $this->l('The PriceTag script is required.');

            return;
        }

        $tagsScript = Config::extractPricetagScriptCode($rawScript);

        if ($tagsScript === '') {
            $this->errors[] = $this->l('The PriceTag script does not contain any inline JavaScript code. Please paste the standard ViaBill PriceTag snippet.');

            return;
        }

        Configuration::updateValue(Config::API_TAGS_SCRIPT, $tagsScript);
    }

    /**
     * Get all order states formatted for select dropdown
     *
     * @return array
     */
    private function getOrderStatesForSelect()
    {
        $orderStates = OrderState::getOrderStates($this->context->language->id);
        
        $formattedStates = [];
        foreach ($orderStates as $state) {
            $formattedStates[] = [
                'id_order_state' => $state['id_order_state'],
                'name' => $state['name'],
            ];
        }
        
        return $formattedStates;
    }

    /**
     * Gets MyViaBill Auto-Login Link.
     *
     * @return bool|string|void
     *
     * @throws Exception
     */
    private function getMyViaBillLink()
    {
        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        if (!$config->isLoggedIn()) {
            return;
        }

        /** @var \ViaBill\Service\Api\Link\LinkService $linkService */
        $linkService = $this->module->getModuleContainer()->get('service.link');
        $linkResponse = $linkService->getLink();

        if ($linkResponse->hasErrors()) {
            $errors = $linkResponse->getErrors();

            foreach ($errors as $error) {
                $errorField = '';

                if ($error->getField()) {
                    $errorField = sprintf($this->l('Field: %s. '), $error->getField());
                }

                $this->context->controller->warnings[] =
                    $errorField . sprintf($this->l('Error: %s '), $error->getError());
            }

            return false;
        }

        return $linkResponse->getLink();
    }

    private function getDebugInfo()
    {
        $html = '<table>';

        try {
            // Get Module Version
            $moduleInstance = Module::getInstanceByName('viabill');
            $module_version = $moduleInstance->version;

            // Get PHP info
            $php_version = phpversion();
            $memory_limit = ini_get('memory_limit');

            // Get Prestashop Version
            $prestashop_version = Configuration::get('PS_VERSION_DB');

            // Log data
            $debug_file_path = DebugLog::getFilename();

            $module_info_data = '<ul>' .
                '<li><strong>' . $this->l('Module Version') . '</strong>: ' . $module_version . '</li>' .
                '<li><strong>' . $this->l('Prestashop Version') . '</strong>: ' . $prestashop_version . '</li>' .
                '<li><strong>' . $this->l('PHP Version') . '</strong>: ' . $php_version . '</li>' .
                '<li><strong>' . $this->l('Memory Limit') . '</strong>: ' . $memory_limit . '</li>' .
                '<li><strong>' . $this->l('OS') . '</strong>: ' . PHP_OS . '</li>' .
                '<li><strong>' . $this->l('Debug File') . '</strong>: ' . $debug_file_path . '</li>' .
                '</ul>';

            $module_params = [
                    'module_version' => $module_version,
                    'prestashop_version' => $prestashop_version,
                    'php_version' => $php_version,
                    'memory_limit' => $memory_limit,
                    'os' => PHP_OS,
                    'debug_file' => $debug_file_path,
                ];

            $email_support = $this->getSupportEmail($module_params);

            $contact_form = $this->getSupportForm();

            $troubleshoot_form = $this->getTroubleshootForm();

            $module_info_data .= $email_support . '<br/>' . $contact_form . '<br/>' . $troubleshoot_form;
        } catch (\Exception $e) {
            $module_info_data = $this->l('N/A');
            DebugLog::msg($e->getMessage(), 'error');
        }

        $html = $module_info_data;

        return $html;
    }

    protected function getSupportEmail($params)
    {
        $site_url = _PS_BASE_URL_;

        $email = self::VIABILL_TECH_SUPPORT_EMAIL;
        $subject = "Prestashop 1.7 - Technical Assistance Needed - {$site_url}";
        $body = "Dear support,\r\nI am having an issue with the ViaBill Payment Module." .
                "\r\nHere is the detailed description:\r\n" .
                "\r\nType here ....\r\n" .
                "\r\n ============================================ " .
                "\r\n[System Info]\r\n" .
                '* Module Version: ' . $params['module_version'] . "\r\n" .
                '* Prestashop Version: ' . $params['prestashop_version'] . "\r\n" .
                '* PHP Version: ' . $params['php_version'] . "\r\n" .
                '* Memory Limit: ' . $params['memory_limit'] . "\r\n" .
                '* OS: ' . $params['os'] . "\r\n" .
                '* Debug File: ' . $params['debug_file'] . "\r\n";

        $html = $this->l('Need support? Contact us at ') .
                '<a href="mailto:' . $email . '?subject=' . rawurlencode($subject) .
                '&body=' . rawurlencode($body) . '">' . $email . '</a>';

        return $html;
    }

    protected function getSupportForm()
    {
        $url = $this->context->link->getAdminLink('AdminViaBillContact');
        $html = $this->l('Or use instead the') . ' <a href="' . $url . '">' . $this->l('Contact form') . '</a>';

        return $html;
    }

    protected function getTroubleshootForm()
    {
        $url = $this->context->link->getAdminLink('AdminViaBillTroubleshoot');
        $html = $this->l('If you are having trouble displaying the PriceTags visit the') . ' <a href="' . $url . '">' . $this->l('Troubleshooting') . '</a>';

        return $html;
    }

    protected function getConflictWarning()
    {
        $conflict_key = Config::MODULE_CONFLICT_THIRD_PARTY_KEY;
        $warning = '';

        if (Configuration::hasKey($conflict_key)) {
            $conflict_found = (int) Configuration::get($conflict_key);
            if ($conflict_found) {
                $html = $this->l('IMPORTANT! You have ViaBill payments enabled through a payment gateway. The ViaBill payment method provided by this ViaBill Prestashop module requires that ViaBill as a payment method is disabled in that gateway. Fortunately, we’ve made it easy for you; simply click the button below and it is instantly disabled (everything else stays enabled, of course)');
                $html .= '<br/><br/><input class="btn btn-danger" style="margin-bottom:10px;" type="button" value="' . $this->l('Disable now') . '" id="DisableThirdPartyPaymentBtn" >';
                $html .= '<input type="hidden" id="thirdparty_disable_url" value="' . $this->context->link->getAdminLink('AdminViaBillConflict') . '" />';

                $warning = '<div class="alert alert-warning">' . $html . '</div>';
            }
        }

        return $warning;
    }

    protected function fileTail($filepath, $num_of_lines = 100)
    {
        $tail = '';

        $file = new \SplFileObject($filepath, 'r');
        $file->seek(PHP_INT_MAX);
        $last_line = $file->key();

        if ($last_line < $num_of_lines) {
            $num_of_lines = $last_line;
        }

        if ($num_of_lines > 0) {
            $lines = new \LimitIterator($file, $last_line - $num_of_lines, $last_line);
            $arr = iterator_to_array($lines);
            $arr = array_reverse($arr);
            $tail = implode('', $arr);
        }

        return $tail;
    }
}
