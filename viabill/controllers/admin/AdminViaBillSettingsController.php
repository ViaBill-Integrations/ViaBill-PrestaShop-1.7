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

            // Stay on the page when a setting was rejected, so that the error
            // is shown instead of the success message.
            if (!empty($this->errors)) {
                return;
            }

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

        /** @var \ViaBill\Service\LegalNote\LegalNoteService $legalNoteService */
        $legalNoteService = $this->module->getModuleContainer()->get('service.legalNote');

        // Shops that installed the module before these settings existed have no
        // value yet; store the defaults so that the form shows them.
        $legalNoteDefaults = [
            Config::LEGAL_NOTE_FONT_STYLE => \ViaBill\Service\LegalNote\LegalNoteService::DEFAULT_FONT_STYLE,
            Config::LEGAL_NOTE_MARGIN_TOP => \ViaBill\Service\LegalNote\LegalNoteService::DEFAULT_MARGIN_TOP,
            Config::LEGAL_NOTE_MARGIN_BOTTOM => \ViaBill\Service\LegalNote\LegalNoteService::DEFAULT_MARGIN_BOTTOM,
            Config::LEGAL_NOTE_MARGIN_LEFT => \ViaBill\Service\LegalNote\LegalNoteService::DEFAULT_MARGIN_LEFT,
        ];
        foreach ($legalNoteDefaults as $legalNoteKey => $legalNoteDefault) {
            if (Configuration::get($legalNoteKey) === false) {
                Configuration::updateValue($legalNoteKey, $legalNoteDefault);
            }
        }

        $legalNoteInfoBlockText =
            $this->l('The legal note is a short information text shown to customers at checkout when ViaBill is selected as the payment method, such as a warning that borrowing money costs money.');

        $legalNoteLanguage = (string) Configuration::get(Config::LEGAL_NOTE_LANGUAGE) ?: 'auto';
        $legalNoteTexts = $legalNoteService->getDefaultTexts();

        $this->fields_options[Config::SETTINGS_LEGAL_NOTE_SECTION] = [
            'title' => $this->l('Legal Note'),
            'icon' => 'icon-legal',
            'fields' => [
                Config::LEGAL_NOTE_INFO_BLOCK_FIELD => [
                    'type' => 'free',
                    'desc' => $this->getInfoBlockTemplate($legalNoteInfoBlockText),
                    'class' => 'hidden',
                    'form_group_class' => 'viabill-info-block',
                ],
                // Read only, see views/templates/admin/field-option-settings/helpers/options/options.tpl.
                Config::LEGAL_NOTE_TEXT => [
                    'title' => $this->l('Text'),
                    'desc' => $this->l('The legal note shown at checkout, in the language selected below.'),
                    'type' => 'viabill_legal_note_text',
                    'text' => isset($legalNoteTexts[$legalNoteLanguage]) ? $legalNoteTexts[$legalNoteLanguage] : $legalNoteTexts['auto'],
                    'texts' => $legalNoteTexts,
                    'language_select' => Config::LEGAL_NOTE_LANGUAGE,
                ],
                Config::LEGAL_NOTE_LANGUAGE => [
                    'title' => $this->l('Language'),
                    'desc' => $this->l('Language of the default legal note. "Auto" uses the language of the customer.'),
                    'type' => 'select',
                    'list' => $legalNoteService->getLanguages(),
                    'identifier' => 'id',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                ],
                Config::LEGAL_NOTE_FONT_SIZE => [
                    'title' => $this->l('Font size'),
                    'desc' => $this->l('Font size of the legal note, e.g. 14px, 0.9em or 90%. Leave empty to use the font size of your theme.'),
                    'type' => 'text',
                    'class' => 'fixed-width-md',
                ],
                Config::LEGAL_NOTE_FONT_STYLE => [
                    'title' => $this->l('Font style'),
                    'type' => 'select',
                    'list' => $legalNoteService->getFontStyles(),
                    'identifier' => 'id',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                ],
                Config::LEGAL_NOTE_COLOR => [
                    'title' => $this->l('Text color'),
                    'desc' => $this->l('Color code of the legal note text, e.g. #cc0000. Leave empty to use the text color of your theme.'),
                    // A plain text field: the color picker of the options form cannot be left empty.
                    'type' => 'text',
                    'class' => 'fixed-width-md',
                    'validation' => 'isColor',
                ],
                Config::LEGAL_NOTE_ALIGN => [
                    'title' => $this->l('Text alignment'),
                    'type' => 'select',
                    'list' => $legalNoteService->getAlignments(),
                    'identifier' => 'id',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                ],
                Config::LEGAL_NOTE_MARGIN_TOP => [
                    'title' => $this->l('Margin top'),
                    'desc' => $this->l('Space above the legal note, e.g. 4px or 0.5em. A negative value moves it closer to the payment method name, e.g. -18px in the Classic theme.'),
                    'type' => 'text',
                    'class' => 'fixed-width-md',
                ],
                Config::LEGAL_NOTE_MARGIN_BOTTOM => [
                    'title' => $this->l('Margin bottom'),
                    'desc' => $this->l('Space below the legal note, e.g. 4px or 0.5em.'),
                    'type' => 'text',
                    'class' => 'fixed-width-md',
                ],
                Config::LEGAL_NOTE_MARGIN_LEFT => [
                    'title' => $this->l('Margin left'),
                    'desc' => $this->l('Space to the left of the legal note, e.g. 4px. A negative value moves it to the left, e.g. -20px to line it up with the ViaBill logo in the Classic theme.'),
                    'type' => 'text',
                    'class' => 'fixed-width-md',
                ],
                Config::LEGAL_NOTE_EXTRA_CSS => [
                    'title' => $this->l('Extra CSS'),
                    'desc' => $this->l('Optional CSS declarations applied to the legal note box, separated by semicolons (no selectors or braces), e.g. padding: 8px 12px; background-color: #fff8e1; border-left: 3px solid #f0b849; They are applied after the options above, so they can also override them.'),
                    'type' => 'textarea',
                    'cols' => 60,
                    'rows' => 3,
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

        // The script is printed unescaped on every storefront page, so only
        // SuperAdmin may change it. Other employees can still save the rest of
        // the settings as long as they leave the script unchanged.
        if ($tagsScript !== (string) Configuration::get(Config::API_TAGS_SCRIPT) &&
            !Config::isSuperAdmin($this->context->employee)
        ) {
            $this->errors[] = $this->l('Only employees with the SuperAdmin profile can change the PriceTag script.');

            return;
        }

        Configuration::updateValue(Config::API_TAGS_SCRIPT, $tagsScript);
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_TEXT option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * The field is read only and shows the default legal note, so nothing is
     * stored. A text that merchants entered in earlier versions is removed.
     *
     * @param mixed $value Unused, the field is not posted.
     */
    public function updateOptionVbLegalNoteText($value)
    {
        Configuration::deleteByName(Config::LEGAL_NOTE_TEXT);
        Configuration::deleteByName(Config::LEGAL_NOTE_ENABLED);
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_MARGIN_TOP option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * @param string $value
     */
    public function updateOptionVbLegalNoteMarginTop($value)
    {
        $this->saveLegalNoteMargin(
            Config::LEGAL_NOTE_MARGIN_TOP,
            $value,
            $this->l('The top margin of the legal note is not valid. Use a value such as 4px, -12px or 0.5em.')
        );
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_MARGIN_BOTTOM option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * @param string $value
     */
    public function updateOptionVbLegalNoteMarginBottom($value)
    {
        $this->saveLegalNoteMargin(
            Config::LEGAL_NOTE_MARGIN_BOTTOM,
            $value,
            $this->l('The bottom margin of the legal note is not valid. Use a value such as 4px or 0.5em.')
        );
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_MARGIN_LEFT option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * @param string $value
     */
    public function updateOptionVbLegalNoteMarginLeft($value)
    {
        $this->saveLegalNoteMargin(
            Config::LEGAL_NOTE_MARGIN_LEFT,
            $value,
            $this->l('The left margin of the legal note is not valid. Use a value such as 4px or -20px.')
        );
    }

    /**
     * Save a margin of the legal note in its normalized form (e.g. "-12"
     * becomes "-12px"). Empty means the default margin.
     *
     * @param string $key Config::LEGAL_NOTE_MARGIN_TOP, _BOTTOM or _LEFT
     * @param string $value
     * @param string $error Error message if the value is not valid
     */
    private function saveLegalNoteMargin($key, $value, $error)
    {
        $value = trim((string) $value);

        /** @var \ViaBill\Service\LegalNote\LegalNoteService $legalNoteService */
        $legalNoteService = $this->module->getModuleContainer()->get('service.legalNote');
        $margin = $legalNoteService->sanitizeMargin($value);

        if ($value !== '' && $margin === '') {
            $this->errors[] = $error;

            return;
        }

        if ($margin === '') {
            $margin = $legalNoteService->getDefaultMargin($key);
        }

        Configuration::updateValue($key, $margin);
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_FONT_SIZE option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * The font size is stored in its normalized form (e.g. "14" becomes
     * "14px"); empty means the font size of the theme.
     *
     * @param string $value
     */
    public function updateOptionVbLegalNoteFontSize($value)
    {
        $value = trim((string) $value);

        /** @var \ViaBill\Service\LegalNote\LegalNoteService $legalNoteService */
        $legalNoteService = $this->module->getModuleContainer()->get('service.legalNote');
        $fontSize = $legalNoteService->sanitizeFontSize($value);

        if ($value !== '' && $fontSize === '') {
            $this->errors[] = $this->l('The font size of the legal note is not valid. Use a value such as 14px, 0.9em or 90%.');

            return;
        }

        Configuration::updateValue(Config::LEGAL_NOTE_FONT_SIZE, $fontSize);
    }

    /**
     * Custom save handler for the VB_LEGAL_NOTE_EXTRA_CSS option (called
     * automatically by AdminController::processUpdateOptions()).
     *
     * Only CSS declarations are kept, see LegalNoteService::sanitizeExtraCss().
     *
     * @param string $value
     */
    public function updateOptionVbLegalNoteExtraCss($value)
    {
        /** @var \ViaBill\Service\LegalNote\LegalNoteService $legalNoteService */
        $legalNoteService = $this->module->getModuleContainer()->get('service.legalNote');

        Configuration::updateValue(
            Config::LEGAL_NOTE_EXTRA_CSS,
            $legalNoteService->sanitizeExtraCss((string) $value)
        );
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
