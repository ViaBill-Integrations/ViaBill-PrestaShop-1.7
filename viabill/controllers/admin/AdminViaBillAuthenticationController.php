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

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * ViaBill Account Credentials Controller Class.
 *
 * The merchant obtains the API key, API secret and PriceTag script
 * elsewhere (e.g. from the ViaBill merchant portal) and enters them here
 * manually. No remote call to the ViaBill server (register/login) is
 * performed by this controller.
 *
 * Class AdminViaBillAuthenticationController
 */
class AdminViaBillAuthenticationController extends ModuleAdminController
{
    /**
     * AdminViaBillAuthenticationController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = Configuration::$definition['table'];
        $this->className = 'Configuration';
        $this->identifier = Configuration::$definition['primary'];
        $this->display = 'add';
        parent::__construct();

        $this->toolbar_title = $this->l('ViaBill Account Credentials');
    }

    /**
     * Init Error Messages From Cookies.
     * Checks If User Is Already Configured And Init Credentials Form.
     *
     * @throws Exception
     */
    public function init()
    {
        if (isset($this->context->cookie->authErrorMessage)) {
            $authErrors = json_decode($this->context->cookie->authErrorMessage);

            foreach ($authErrors as $authError) {
                $this->errors[] = $authError;
            }

            unset($this->context->cookie->authErrorMessage);
        }

        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        /**
         * @var \ViaBill\Install\Tab $tab
         */
        $tab = $this->module->getModuleContainer()->get('tab');

        if ($config->isLoggedIn()) {
            // The credentials can be updated at any time from the top of the
            // ViaBill Settings page.
            Tools::redirectAdmin($this->context->link->getAdminLink($tab->getControllerSettingsName()));
        }

        $this->getCredentialsForm();

        parent::init();
    }

    /**
     * Adds CSS Files To ViaBill Account Credentials Controller.
     *
     * @param bool $isNewTheme
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS($this->module->getLocalPath() . '/views/css/admin/authentication.css');
        $this->addCSS($this->module->getLocalPath() . '/views/css/admin/info-block.css');
    }

    /**
     * Credentials Form Validation And Saving.
     *
     * @return bool|ObjectModel
     *
     * @throws Exception
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitCredentialsForm')) {
            $errorsArray = [];

            $apiKey = trim((string) Tools::getValue('viabill_api_key'));
            $apiSecret = trim((string) Tools::getValue('viabill_api_secret'));
            $rawScript = trim((string) Tools::getValue('viabill_pricetag_script'));
            $tagsScript = Config::extractPricetagScriptCode($rawScript);

            // Allow the merchant to keep the already stored secret by
            // leaving the (masked) secret field empty.
            if ($apiSecret === '') {
                $apiSecret = (string) Configuration::get(Config::API_SECRET);
            }

            if ($apiKey === '') {
                $errorsArray[] = $this->l('The API key is required.');
            }

            if ($apiSecret === '') {
                $errorsArray[] = $this->l('The API secret is required.');
            }

            if ($rawScript === '') {
                $errorsArray[] = $this->l('The PriceTag script is required.');
            } elseif ($tagsScript === '') {
                $errorsArray[] = $this->l('The PriceTag script does not contain any inline JavaScript code. Please paste the standard ViaBill PriceTag snippet.');
            }

            if (!empty($errorsArray)) {
                $this->context->cookie->authErrorMessage = json_encode($errorsArray);

                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminViaBillAuthentication')
                );

                return parent::postProcess();
            }

            $this->saveCredentials($apiKey, $apiSecret, $tagsScript);
        }

        return parent::postProcess();
    }

    /**
     * Init Credentials Form Values.
     *
     * @return string
     *
     * @throws SmartyException
     */
    public function renderForm()
    {
        $this->initCredentialsFormValues();

        return parent::renderForm();
    }

    /**
     * Account Credentials Form Formation.
     */
    protected function getCredentialsForm()
    {
        $credentialsInfoBlockText =
            $this->l('Enter your ViaBill API key, API secret and PriceTag script below. You can find these values in your ViaBill merchant account. All three values are required for the payment gateway and the PriceTags to work.');

        $hasStoredSecret = (bool) Configuration::get(Config::API_SECRET);

        $secretDesc = $hasStoredSecret
            ? $this->l('An API secret is already saved (hidden for security). Leave this field empty to keep it.')
            : $this->l('Your ViaBill API secret.');

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('ViaBill Account Credentials'),
            ],
            'input' => [
                [
                    'type' => 'free',
                    'name' => 'credentials_hint',
                    'desc' => $this->getInfoBlockTemplate($credentialsInfoBlockText),
                    'class' => 'hidden',
                    'form_group_class' => 'viabill-info-block',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'name' => 'viabill_api_key',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                ],
                [
                    'type' => 'password',
                    'label' => $this->l('API Secret'),
                    'name' => 'viabill_api_secret',
                    'class' => 'fixed-width-xxl',
                    'desc' => $secretDesc,
                    'required' => !$hasStoredSecret,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('PriceTag Script'),
                    'name' => 'viabill_pricetag_script',
                    'cols' => 60,
                    'rows' => 5,
                    'desc' => $this->l('Paste the whole PriceTag script snippet provided by ViaBill. You may include the opening and closing script tags; they will be removed automatically, since the module adds its own.'),
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save Credentials'),
                'icon' => 'process-icon-ok',
                'name' => 'submitCredentialsForm',
            ],
        ];
    }

    /**
     * Saves The Manually Entered Credentials And Finishes The Setup.
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $tagsScript
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function saveCredentials($apiKey, $apiSecret, $tagsScript)
    {
        Configuration::updateValue(Config::API_KEY, $apiKey);
        Configuration::updateValue(Config::API_SECRET, $apiSecret);
        Configuration::updateValue(Config::API_TAGS_SCRIPT, $tagsScript);

        $this->context->cookie->authSuccessMessage = $this->l('ViaBill account credentials saved successfully');
        if (!$this->saveModuleRestrictions()) {
            return false;
        }

        /**
         * @var \ViaBill\Install\Tab $tab
         */
        $tab = $this->module->getModuleContainer()->get('tab');
        $authenticationTab = Tab::getInstanceFromClassName($tab->getControllerAuthenticationName());
        $authenticationTab->active = false;
        $authenticationTab->id_parent = -1;
        $authenticationTab->update();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminViaBillSettings'));

        return true;
    }

    /**
     * Init Credentials Form Values.
     */
    protected function initCredentialsFormValues()
    {
        $this->fields_value['viabill_api_key'] = Configuration::get(Config::API_KEY) ?: '';
        // The API secret is never printed back to the page.
        $this->fields_value['viabill_api_secret'] = '';
        $this->fields_value['viabill_pricetag_script'] = Configuration::get(Config::API_TAGS_SCRIPT) ?: '';
    }

    /**
     * Adding Country And Currency Restrictions.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function saveModuleRestrictions()
    {
        /** @var \ViaBill\Service\Handler\ModuleRestrictionHandler $restrictionHandler */
        $restrictionHandler = $this->module->getModuleContainer()->get('service.handler.moduleRestriction');
        $warnings = [];

        $failedCountry =
        $this->l('Unable to save module country restrictions. It can be done manually in payment preferences tab.');

        $failedCurrency =
        $this->l('Unable to save module currency restrictions. It can be done manually in payment preferences tab.');

        if (!$restrictionHandler->saveCountryRestriction($this->context->language)) {
            $warnings[] = $failedCountry;
        }

        if (!$restrictionHandler->saveCurrencyRestriction()) {
            $warnings[] = $failedCurrency;
        }

        $result = true;
        if (!empty($warnings)) {
            $this->context->controller->warnings = $warnings;
            $result = false;
        }

        return $result;
    }
}
