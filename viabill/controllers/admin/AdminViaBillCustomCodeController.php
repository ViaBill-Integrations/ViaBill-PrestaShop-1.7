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
use ViaBill\Util\DebugLog;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * ViaBill Custom Code Controller Class.
 * Allows administrators to inject custom CSS and JavaScript code.
 *
 * Class AdminViaBillCustomCodeController
 */
class AdminViaBillCustomCodeController extends ModuleAdminController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    /**
     * AdminViaBillCustomCodeController constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();
    }

    /**
     * Initialize the controller
     */
    public function init()
    {
        if (isset($this->context->cookie->saveSuccessMessage)) {
            $this->confirmations[] = $this->context->cookie->saveSuccessMessage;
            unset($this->context->cookie->saveSuccessMessage);
        }

        parent::init();
    }

    /**
     * Initialize content
     */
    public function initContent()
    {
        $this->content = $this->renderView() . $this->renderForm();

        return parent::initContent();
    }

    /**
     * Process form submission
     *
     * @return bool|ObjectModel|void
     */
    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitViaBillCustomCode')) {
            $customCSS = Tools::getValue('VIABILL_CUSTOM_CSS');
            $customJS = Tools::getValue('VIABILL_CUSTOM_JS');

            Configuration::updateValue('VIABILL_CUSTOM_CSS', $customCSS);
            Configuration::updateValue('VIABILL_CUSTOM_JS', $customJS);

            $this->context->cookie->saveSuccessMessage = $this->l('Custom code settings have been successfully updated.');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminViaBillCustomCode'));
        }
    }

    /**
     * Add CSS and JS files to the controller
     *
     * @param bool $isNewTheme
     *
     * @throws Exception
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
        }

        // Only add CSS/JS if files exist
        $cssPath = $this->module->getLocalPath() . 'views/css/admin/custom-code.css';
        $jsPath = $this->module->getLocalPath() . 'views/js/admin/custom-code.js';

        if (file_exists($cssPath)) {
            $this->addCSS($this->module->getLocalPath() . 'views/css/admin/custom-code.css');
        }

        if (file_exists($jsPath)) {
            $this->addJS($this->module->getLocalPath() . 'views/js/admin/custom-code.js');
        }
    }

    /**
     * Render the configuration form
     *
     * @return string
     */
    public function renderForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Build the form fields
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Custom CSS and JavaScript Settings'),
                    'icon' => 'icon-code'
                ],
                'description' => $this->l('Add custom CSS and JavaScript code to customize your ViaBill integration. This code will be injected into all front-end pages.'),
                'input' => [
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS Code'),
                        'name' => 'VIABILL_CUSTOM_CSS',
                        'desc' => $this->l('Enter your custom CSS code here (without <style> tags). It will be inserted in the page header.'),
                        'rows' => 15,
                        'cols' => 100,
                        'class' => 'viabill-code-editor',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom JavaScript Code'),
                        'name' => 'VIABILL_CUSTOM_JS',
                        'desc' => $this->l('Enter your custom JavaScript code here (without <script> tags). It will be inserted in the page footer.'),
                        'rows' => 15,
                        'cols' => 100,
                        'class' => 'viabill-code-editor',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ]
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminViaBillCustomCode');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminViaBillCustomCode', false);

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitViaBillCustomCode';

        // Load current values
        $helper->fields_value['VIABILL_CUSTOM_CSS'] = Configuration::get('VIABILL_CUSTOM_CSS');
        $helper->fields_value['VIABILL_CUSTOM_JS'] = Configuration::get('VIABILL_CUSTOM_JS');

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Render the page content
     *
     * @return string
     */
    public function renderView()
    {
        // Create info box HTML directly
        $infoHtml = '
        <div class="alert alert-info">
            <h4><i class="icon-info-circle"></i> ' . $this->l('Custom CSS and JavaScript Code') . '</h4>
            <p>' . $this->l('This feature allows you to add custom CSS and JavaScript code to enhance your ViaBill integration without modifying theme files.') . '</p>
            <ul>
                <li>' . $this->l('Customize the appearance of ViaBill price tags') . '</li>
                <li>' . $this->l('Add custom styling to payment buttons') . '</li>
                <li>' . $this->l('Integrate third-party analytics or tracking scripts') . '</li>
                <li>' . $this->l('Implement custom behavior for the checkout process') . '</li>
            </ul>
        </div>
        
        <div class="alert alert-warning">
            <h4><i class="icon-warning"></i> ' . $this->l('Important Notes') . '</h4>
            <p>' . $this->l('Do NOT include <style> or <script> tags in your code - they will be added automatically.') . '</p>
            <p>' . $this->l('Always test your custom code thoroughly before deploying to production.') . '</p>
            <p>' . $this->l('Invalid code may break your website functionality.') . '</p>
        </div>
        
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-file-code-o"></i> ' . $this->l('Code Examples') . '
            </div>
            <div class="panel-body">
                <h4>' . $this->l('Example 1: Customize ViaBill Price Tag Colors') . '</h4>
                <p>' . $this->l('Add this to the Custom CSS Code field:') . '</p>
                <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">/* Customize ViaBill price tag appearance */
.viabill-pricetag {
    color: #9b26b7 !important;
    font-weight: bold;
}

.viabill-pricetag a {
    color: #25b9d7 !important;
    text-decoration: underline;
}</pre>
                
                <h4>' . $this->l('Example 2: Track ViaBill Payment Selection') . '</h4>
                <p>' . $this->l('Add this to the Custom JavaScript Code field:') . '</p>
                <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">// Track when ViaBill payment method is selected
$(document).ready(function() {
    $(\'input[name="payment-option"]\').on(\'change\', function() {
        if ($(this).data(\'module-name\') === \'viabill\') {
            console.log(\'ViaBill payment method selected\');
            // Add your tracking code here
        }
    });
});</pre>
            </div>
        </div>';
        
        return $infoHtml;
    }
}