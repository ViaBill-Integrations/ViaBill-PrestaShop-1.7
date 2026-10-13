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
 * ViaBill Contact Controller Class.
 *
 * Class AdminViaBillContactController
 */
class AdminViaBillContactController extends ModuleAdminController
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    /**
     * Contact email address
     */
    const VIABILL_TECH_SUPPORT_EMAIL = 'tech@viabill.com';

    /**
     * Number of lines to read from the log files (debug and error).
     */
    const LOG_FILE_LINES_TO_READ = 150;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();
    }

    /**
     * Calls Redirect To Login Method.
     */
    public function init()
    {
        $this->redirectToLogin();
        parent::init();
    }

    /**
     * Redirects To Authentication Tab If User Is Not Loggen In.
     *
     * @throws \Exception
     */
    private function redirectToLogin()
    {
        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        if ($config->isLoggedIn()) {
            return;
        }

        /**
         * @var Tab $tab
         */
        $tab = $this->module->getModuleContainer()->get('tab');

        \Tools::redirectAdmin(
            $this->context->link->getAdminLink($tab->getControllerAuthenticationName())
        );
    }

    public function initContent()
    {
        /**
         * @var \ViaBill\Builder\Template\ContactTemplate $contactTemplate
         */
        $contactTemplate = $this->module->getModuleContainer()->get('builder.template.contact');
        $contactTemplate->setSmarty($this->context->smarty);

        if (Tools::getValue('ticket_info')) {
            // Sending the support request emails shop data: only accept POST
            // requests with a valid back office token. The core check is used
            // because, depending on the PrestaShop version and the "token in
            // URLs" setting, the token is the legacy 'token' parameter or absent.
            if (
                !isset($_SERVER['REQUEST_METHOD']) ||
                strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST' ||
                !$this->checkToken()
            ) {
                $this->content = "<div class='alert alert-danger'><div class='alert-text'>
                    <strong>" . $this->l('Error') . '</strong><br/>' .
                    $this->l('Form token mismatch detected.') .
                    '</div></div>';

                return parent::initContent();
            }

            $this->content = $this->getContactFormOutput();
        } else {
            $params = $this->getContactForm();
            if (isset($params['error'])) {
                $this->content = "<div class='alert alert-danger'><div class='alert-text'>
                    <strong>" . $this->l('Error') . '</strong><br/>' .
                    $params['error'] .
                    '</div></div>';
            } else {
                $contactTemplate->setSmartyParams($params);
                $this->content = $contactTemplate->getHtml();
            }
        }

        return parent::initContent();
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

        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
        }
    }

    protected function getContactForm()
    {
        $params = [];

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

            // Get Store Info
            $langCode = $this->context->language->iso_code;
            $currencyCode = $this->context->currency->iso_code;
            $storeName = Configuration::get('PS_SHOP_NAME');
            $storeURL = _PS_BASE_URL_;

            // Get ViaBill Config
            $storeCountry = $this->context->country->iso_code;

            $storeEmail = Configuration::get('PS_SHOP_EMAIL');
            if (empty($storeEmail)) {
                $employee = $this->context->employee;
                $storeEmail = $employee->email;
            }

            $apiKey = Configuration::get('VB_API_KEY');

            $file_lines = self::LOG_FILE_LINES_TO_READ;

            $debug_log_entries = 'N/A';
            if (file_exists($debug_file_path)) {
                $debug_log_entries = $this->fileTail($debug_file_path, $file_lines);
            }

            $action_url = $this->getActionURL();

            $terms_of_service_lang = Tools::strtolower(trim($langCode));
            switch ($terms_of_service_lang) {
                case 'us':
                    $terms_of_use_url = 'https://viabill.com/us/legal/cooperation-agreement/';
                    break;
                case 'es':
                    $terms_of_use_url = 'https://viabill.com/es/legal/contrato-cooperacion/';
                    break;
                case 'dk':
                    $terms_of_use_url = 'https://viabill.com/dk/legal/cooperation-agreement/';
                    break;
                default:
                    $terms_of_use_url = 'https://viabill.com/dk/legal/cooperation-agreement/';
                    break;
            }

            $token = $this->token;

            $params = [
                'module_version' => $module_version,
                'prestashop_version' => $prestashop_version,
                'php_version' => $php_version,
                'memory_limit' => $memory_limit,
                'os' => PHP_OS,
                'debug_file' => $debug_file_path,
                'debug_log_entries' => $debug_log_entries,
                'action_url' => $action_url,
                'token' => $token,
                'terms_of_use_url' => $terms_of_use_url,
                'langCode' => $langCode,
                'currencyCode' => $currencyCode,
                'storeName' => $storeName,
                'storeURL' => $storeURL,
                'apiKey' => $apiKey,
                'storeEmail' => $storeEmail,
                'storeCountry' => $storeCountry,
            ];
        } catch (\Exception $e) {
            DebugLog::msg($e->getMessage(), 'error');

            $params['error'] = $e->getMessage();

            return $params;
        }

        return $params;
    }

    protected function getContactFormOutput()
    {
        $request = $_REQUEST;

        $ticket_info = (isset($request['ticket_info']) && is_array($request['ticket_info'])) ? $request['ticket_info'] : [];
        $shop_info = (isset($request['shop_info']) && is_array($request['shop_info'])) ? $request['shop_info'] : [];

        $platform = $this->getRequestString($shop_info, 'platform');
        $shop_url = $this->getRequestString($shop_info, 'url');

        // Only a valid address is ever used; it ends up in a mail header.
        $merchant_email = filter_var(trim($this->getRequestString($ticket_info, 'email')), FILTER_VALIDATE_EMAIL);
        $support_email = self::VIABILL_TECH_SUPPORT_EMAIL;

        if (!$merchant_email) {
            return "<div class='alert alert-danger'><div class='alert-text'>
                <strong>" . $this->l('Error') . '</strong><br/>' .
                $this->l('Please enter a valid email address.') .
                '</div></div>';
        }

        $shop_info_html = '<ul>';
        foreach ($shop_info as $key => $value) {
            $label = $this->escapeHtml(Tools::strtoupper(str_replace('_', ' ', (string) $key)));
            $value = $this->escapeHtml(is_scalar($value) ? (string) $value : '');
            if ($key == 'debug_data') {
                $shop_info_html .= '<li><strong>' . $label . '</strong><br/>
                <div style="background-color: #FFFFCC;">' .
                    $value . '</div></li>';
            } elseif ($key == 'error_data') {
                $shop_info_html .= '<li><strong>' . $label . '</strong><br/>
                <div style="background-color: #FFCCCC;">' .
                    $value . '</div>
                </li>';
            } else {
                $shop_info_html .= '<li><strong>' . $label . '</strong>: ' . $value . '</li>';
            }
        }
        $shop_info_html .= '</ul>';

        $email_subject = $this->stripLineBreaks(
            'New ' . Tools::ucfirst($platform) . ' Support Request from ' . $shop_url
        );
        $email_body = "Dear support,\n<br/>You have received a new support request with " .
                       "the following details:\n";
        $email_body .= '<h3>Ticket</h3>';
        $email_body .= '<table>';
        $email_body .= "<tr><td style='background: #eee;'><strong>Name:</strong></td><td>" .
            $this->escapeHtml($this->getRequestString($ticket_info, 'name')) . '</td></tr>';
        $email_body .= "<tr><td style='background: #eee;'><strong>Email:</strong></td><td>" .
            $this->escapeHtml($merchant_email) . '</td></tr>';
        $email_body .= "<tr><td style='background: #eee;'><strong>Issue:</strong></td><td>" .
            nl2br($this->escapeHtml($this->getRequestString($ticket_info, 'issue'))) . '</td></tr>';
        $email_body .= '</table>';
        $email_body .= '<h3>Shop Info</h3>';
        $email_body .= $shop_info_html;

        // Send from an address of the shop's own domain and let support reply
        // to the merchant, instead of spoofing the merchant's address.
        $sender_email = $this->getSenderEmail($request);
        $to = $support_email;

        $success = $this->sendMail($to, $sender_email, $merchant_email, $email_subject, $email_body);
        if (!$success) {
            // use another method
            $success = $this->sendMail($to, $sender_email, $merchant_email, $email_subject, $email_body, true);
        }

        if ($success) {
            $success_msg = '';
            $success_msg = $this->l('Your request has been received successfully!') .
                $this->l('We will get back to you soon at ') . '<strong>' . $this->escapeHtml($merchant_email) . '</strong>. ' .
                $this->l('You may also contact us at ') . "<strong>{$support_email}</strong>.";
            $body = "<div class='alert alert-success'><div class='alert-text'>
                <strong>" . $this->l('Success!') . '</strong><br/>' .
                $success_msg .
                '</div></div>';
        } else {
            $fail_msg = $this->l('Could not email your request form to the technical support team. ') .
                $this->l('Please try again or contact us at ') . "<strong>{$support_email}</strong>.";
            $body = "<div class='alert alert-danger'><div class='alert-text'>
                <strong>" . $this->l('Error') . '</strong><br/>' .
                $fail_msg .
                '</div></div>';
        }

        $html = $body;

        return $html;
    }

    protected function sendMail($to, $from, $replyTo, $email_subject, $email_body, $usePrestashopAPI = false)
    {
        $success = false;

        // Header values must never contain line breaks (header injection).
        $from = $this->stripLineBreaks($from);
        $replyTo = $this->stripLineBreaks($replyTo);
        $email_subject = $this->stripLineBreaks($email_subject);

        if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($usePrestashopAPI) {
            $success = Mail::Send(
                (int) (Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                'contact', // email template file to be use
                $email_subject, // email subject
                [
                    '{email}' => $replyTo, // merchant email address
                    '{message}' => $email_body, // email content
                ],
                $to, // receiver email address
                'Viabill Tech Support', //receiver name
                null, //from email address
                null, //from name
                null, // file attachment
                null, // mode smtp
                _PS_MAIL_DIR_, // template path
                false, // die
                null, // id shop
                null, // bcc
                $replyTo // reply to
            );
        } else {
            if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $headers = 'From: ' . $from . "\r\n";
            $headers .= 'Reply-To: ' . $replyTo . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $phpMailer = 'mail';
            $success = $phpMailer($to, $email_subject, $email_body, $headers);
        }

        return $success;
    }

    /**
     * Gets A String Value From A Request Array.
     *
     * @param array $data
     * @param string $key
     *
     * @return string
     */
    private function getRequestString(array $data, $key)
    {
        return (isset($data[$key]) && is_scalar($data[$key])) ? (string) $data[$key] : '';
    }

    /**
     * Escapes A Value For Output In HTML.
     *
     * @param string $value
     *
     * @return string
     */
    private function escapeHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Removes Line Breaks So A Value Is Safe To Use In A Mail Header.
     *
     * @param string $value
     *
     * @return string
     */
    private function stripLineBreaks($value)
    {
        return trim(str_replace(["\r", "\n", '%0a', '%0d', '%0A', '%0D'], ' ', (string) $value));
    }

    protected function getActionURL()
    {
        $url = $this->context->link->getAdminLink('AdminViaBillContact');

        return $url;
    }

    protected function getSenderEmail($request)
    {
        $senderEmail = '';

        $site_host = _PS_BASE_URL_;

        $merchant_email = '';
        if (isset($request['ticket_info'])) {
            $ticket_info = $request['ticket_info'];
            if (isset($ticket_info['email'])) {
                $merchant_email = filter_var(trim($ticket_info['email']), FILTER_VALIDATE_EMAIL);
            }
        }

        // check if merchant email shares the same domain with the site host
        if (!empty($merchant_email)) {
            list($account, $domain) = explode('@', $merchant_email, 2);
            if (strpos($site_host, $domain) !== false) {
                $senderEmail = $merchant_email;
            }
        }

        if (empty($senderEmail)) {
            $senderEmail = Configuration::get('PS_SHOP_EMAIL');
        }

        // sanity check
        if (empty($senderEmail)) {
            $domain_name = $site_host;

            if (strpos($site_host, '/') !== false) {
                $parts = explode('/', $site_host);
                foreach ($parts as $part) {
                    if (strpos($part, '.') !== false) {
                        $domain_name = $part;
                        break;
                    }
                }
            }

            $parts = explode('.', $domain_name);
            $parts_n = count($parts);
            $sep = '';
            $senderEmail = 'reply@';
            for ($i = ($parts_n - 2); $i < $parts_n; ++$i) {
                $senderEmail .= $sep . $parts[$i];
                $sep = '.';
            }
        }

        return $senderEmail;
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
