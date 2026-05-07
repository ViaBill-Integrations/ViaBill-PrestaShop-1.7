<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
 * @copyright Copyright (c) Viabill
 * @license   Addons PrestaShop license limitation
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use ViaBill\Adapter\Media;
use ViaBill\Config\Config;
use ViaBill\Util\DebugLog;

/**
 * Main Module Class
 *
 * Class ViaBill
 */
class ViaBill extends PaymentModule
{
    /**
     * Symfony DI Container Cache
     */
    const DISABLE_CACHE = true;

    /**
     * Symfony DI Container
     *
     * @var ViaBillContainer
     */
    private $moduleContainer;

    /**
     * Back-office tabs definition for PrestaShop 1.7, 8.x and 9.x.
     *
     * @var array
     */
    protected $tabs = [
        // Invisible root tab under Modules
        [
            'name' => 'ViaBill',
            'class_name' => 'AdminViaBillTabs',
            'visible' => false,
            'parent_class_name' => 'AdminParentModulesSf',
        ],

        // Hidden Ajax controller (root under Modules)
        [
            'name' => 'ViaBill Ajax',
            'class_name' => 'AdminViaBillActions',
            'visible' => false,
            'parent_class_name' => 'AdminParentModulesSf',
        ],

        // Visible children under the invisible ViaBill root
        [
            'name' => 'ViaBill Authentication',
            'class_name' => 'AdminViaBillAuthentication',
            'visible' => true,
            'parent_class_name' => 'AdminViaBillTabs',
        ],
        [
            'name' => 'ViaBill Settings',
            'class_name' => 'AdminViaBillSettings',
            'visible' => true,
            'parent_class_name' => 'AdminViaBillTabs',
        ],
        [
            'name' => 'ViaBill Custom CSS/JS',
            'class_name' => 'AdminViaBillCustomCode',
            'visible' => true,
            'parent_class_name' => 'AdminViaBillTabs',
        ],
        [
            'name' => 'ViaBill Contact',
            'class_name' => 'AdminViaBillContact',
            'visible' => true,
            'parent_class_name' => 'AdminViaBillTabs',
        ],
        [
            'name' => 'ViaBill Troubleshooting',
            'class_name' => 'AdminViaBillTroubleshoot',
            'visible' => true,
            'parent_class_name' => 'AdminViaBillTabs',
        ],
    ];

    /**
     * ViaBill constructor.
     */
    public function __construct()
    {
        $this->name = 'viabill';
        $this->author = 'Written for or by ViaBill';
        $this->description = 'ViaBill Official';
        $this->tab = 'payments_gateways';
        $this->displayName = $this->l('ViaBill');
        $this->version = '9.1.0';
        $this->ps_versions_compliancy = ['min' => '1.7.3.0', 'max' => _PS_VERSION_];
        $this->module_key = '026cfbb4e50aac4d9074eb7c9ddc2584';

        parent::__construct();

        $this->autoLoad();
        $this->compile();
    }    

    /**
     * ViaBill Module Installation Method
     *
     * @return bool
     *
     * @throws Exception
     */
    public function install()
    {
        /** @var \ViaBill\Install\Installer $installer */
        $installer = $this->getModuleContainer()->get('installer');

        // Run core install + custom installer
        if (!parent::install() || !$installer->install()) {
            return false;
        }

        // Legacy 1.7.x behavior: only register this hook on non-Symfony FO
        if (!Config::isVersionAbove177()) {
            $this->registerHook('displayBackOfficeHeader');
        }

        // Do NOT use SymfonyContainer / ModuleTabRegister here.
        // Tabs are handled by the $tabs property on PrestaShop 1.7, 8.x and 9.x.

        // Initialize custom CSS/JS configuration values, but never break install
        try {
            if (Configuration::get('VIABILL_CUSTOM_CSS') === false) {
                Configuration::updateValue('VIABILL_CUSTOM_CSS', '');
            }
            if (Configuration::get('VIABILL_CUSTOM_JS') === false) {
                Configuration::updateValue('VIABILL_CUSTOM_JS', '');
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('ViaBill config init failed: '.$e->getMessage(), 3);
        }

        return true;
    }

    /**
     * ViaBill Module Uninstall Method
     *
     * @return bool
     *
     * @throws Exception
     */
    public function uninstall()
    {                
        /**
         * @var \ViaBill\Install\UnInstaller $unInstaller
         */
        $unInstaller = $this->getModuleContainer()->get('unInstaller');

         // Initialize custom CSS/JS configuration values, but never break install
        try {
            Configuration::deleteByName('VIABILL_CUSTOM_CSS');
            Configuration::deleteByName('VIABILL_CUSTOM_JS');
        } catch (Exception $e) {
            PrestaShopLogger::addLog('ViaBill config init failed: '.$e->getMessage(), 3);
        }    

        return parent::uninstall() && $unInstaller->uninstall();
    }

    /**
     * Getting BO Tabs From Install Folder
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTabs()
    {
        /**
         * @var \ViaBill\Install\Tab $tab
         */
        
        return $this->tabs;
    }

    /**
     * Getting Controller Settings Name
     * Redirecting To Settings Controller
     *
     * @throws Exception
     */
    public function getContent()
    {
        /**
         * @var \ViaBill\Install\Tab $tab
         */
        $tab = $this->getModuleContainer()->get('tab');

        Tools::redirectAdmin($this->context->link->getAdminLink($tab->getControllerSettingsName()));
    }

    /**
     * Checks Is Price Tag Active.
     * Checks Is Valid Controller.
     * Checks If User Is Logged In.
     *
     * @param string $controllerName
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function isPriceTagActive($controllerName)
    {
        $cacheKey = __CLASS__ . __FUNCTION__ . '' . $controllerName;

        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
        }

        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $this->getModuleContainer()->get('config');
        /**
         * @var \ViaBill\Service\Validator\Payment\CurrencyValidator $currencyValidator
         */
        $currencyValidator = $this->getModuleContainer()->get('service.validator.currency');

        /** @var \ViaBill\Service\Validator\LocaleValidator $localeValidator */
        $localeValidator = $this->getModuleContainer()->get('service.validator.locale');

        $isValidController = in_array($controllerName, Config::getTagsControllers());
        $isTagActive = Config::isPriceTagActive($controllerName);

        if (!$isValidController || !$isTagActive) {
            return false;
        }

        $isLogged = $config->isLoggedIn();
        $isCurrencyMatches = $currencyValidator->isCurrencyMatches($this->context->currency);
        $isLocaleMatches = $localeValidator->isLocaleMatches($this->context->language);

        $isDisplayed = $isLogged && $isCurrencyMatches && $isLocaleMatches;
        Cache::store($cacheKey, $isDisplayed);

        return $isDisplayed;
    }

    /**
     * Setting CSS And JS Files For Front Controller.
     *
     * @throws Exception
     */
    public function hookActionFrontControllerSetMedia()
    {
        /**
         * @var \ViaBill\Adapter\Media $mediaAdapter
         */
        $mediaAdapter = $this->getModuleContainer()->get('adapter.media');

        if ($this->context->controller instanceof ViaBillReturnModuleFrontController) {
            $mediaAdapter->addCss($this->context, 'views/css/front/return.css');
        }

        if (!$this->isPriceTagActive($this->context->controller->php_self)) {
            return;
        }

        if ($this->context->controller->php_self == 'product') {
            $this->initPriceTagsProductPage($mediaAdapter);
        }

        if ($this->context->controller->php_self == 'cart') {
            $this->initPriceTagsCartPage($mediaAdapter);
        }

        if ($this->context->controller->php_self == 'order') {
            $mediaAdapter->addJs($this->context, 'views/js/front/payment_option.js');
        }
    }

    /**
     * Setting CSS And JS Files For Admin Order Controller.
     *
     * @throws Exception
     */
    public function hookActionAdminControllerSetMedia()
    {
        if ('AdminOrders' !== Tools::getValue('controller')) {
            return false;
        }

        /**
         * @var \ViaBill\Adapter\Media $mediaAdapter
         */
        $mediaAdapter = $this->getModuleContainer()->get('adapter.media');
        $mediaAdapter->addJsAdmin($this->context, 'viabill-confirmation-message.js');

        $mediaAdapter->addCssAdmin($this->context, 'info-block.css');

        $orderId = Tools::getValue('id_order');
        $order = new Order($orderId);

        if (!$this->isViabillOrder($order)) {
            return false;
        }

        /**
         * @var \ViaBill\Builder\Message\OrderMessageBuilder $messageBuilder
         */
        $messageBuilder = $this->getModuleContainer()->get('builder.message.order');
        $messageBuilder->setContext($this->context);
        $messageBuilder->displayConfirmationMessage();
        $messageBuilder->displayErrorMessage();
        $messageBuilder->displayWarningMessage();
    }

    /**
     * Allows To Add AutoLoad Only To Admin Controllers.
     *
     * @throws Exception
     */
    public function hookModuleRoutes()
    {
        $tabs = $this->tabs; // use property, not service
        $controllers = [];

        foreach ($tabs as $tab) {
            $controllers[] = $tab['class_name'];
        }

        if (empty($controllers)) {
            return;
        }

        if (in_array(Tools::getValue('controller'), $controllers)) {
            $this->autoLoad();
        }
    }

    /**
     * @param array $params
     *
     * @return false|string|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrder(array $params)
    {
        return !Config::isVersionAbove177() ? $this->displayAdminOrderContent($params) : false;
    }

    /**
     * @param array $params
     *
     * @return false|string|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrderTabContent(array $params)
    {
        return Config::isVersionAbove177() ? $this->displayAdminOrderContent($params) : false;
    }

    /**
     * @param array $params
     *
     * @return string|void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *                             Adds tab in admin order page
     */
    private function displayAdminOrderContent(array $params)
    {
        $order = new Order($params['id_order']);

        if (!$this->isViabillOrder($order)) {
            return false;
        }

        $idViaBillOrder = ViaBillOrder::getPrimaryKey($order->id);
        $viaBillOrder = new ViaBillOrder($idViaBillOrder);

        $isOrderAccepted = Validate::isLoadedObject($viaBillOrder);

        if (!$isOrderAccepted) {
            return;
        }

        /**
         * @var \ViaBill\Builder\Template\PaymentManagementTemplate $paymentTemplate
         */
        $paymentTemplate = $this->getModuleContainer()->get('builder.template.paymentManagement');
        $paymentTemplate->setSmarty($this->context->smarty);
        $paymentTemplate->setLanguage($this->context->language);
        $paymentTemplate->setOrder($order);

        /**
         * @var \ViaBill\Install\Tab $tab
         */
        $tab = $this->getModuleContainer()->get('tab');

        $paymentTemplate->setFormAction(
            $this->context->link->getAdminLink(
                $tab->getControllerActionsName(),
                true,
                [],
                [
                    'id_order' => $order->id,
                    'action' => 'handleViaBillOrder',
                ]
            )
        );

        $returnTemplate = '';
        try {
            $returnTemplate = $paymentTemplate->getHtml();
        } catch (Exception $exception) {
            /**
             * @var \ViaBill\Adapter\Tools $toolsAdapter
             */
            $toolsAdapter = $this->getModuleContainer()->get('adapter.tools');
            /**
             * @var string[] $errors
             */
            $errors = json_decode($exception->getMessage());

            foreach ($errors as $error) {
                $this->context->controller->errors[] = $error;
            }
        }

        return $returnTemplate;
    }

    /**
     * Builds New Payment Option And Adds It To Payment Options.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookPaymentOptions($params)
    {
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $this->getModuleContainer()->get('config');

        if (!$config->isLoggedIn()) {
            return [];
        }

        /**
         * @var Cart $cart
         */
        $cart = $params['cart'];
        $currency = new Currency($cart->id_currency);
        $language = new Language($cart->id_lang);

        /**
         * @var \ViaBill\Builder\Payment\PaymentOptionsBuilder $paymentOptionBuilder
         */
        $paymentOptionBuilder = $this->getModuleContainer()->get('builder.payment.paymentOption');
        $paymentOptionBuilder->setLink($this->context->link);
        $paymentOptionBuilder->setCurrency($currency);
        $paymentOptionBuilder->setLanguage($language);
        $paymentOptionBuilder->setSmarty($this->context->smarty);
        $paymentOptionBuilder->setController($this->context->controller->php_self);
        $paymentOptionBuilder->setOrderPrice($cart->getOrderTotal());

        return $paymentOptionBuilder->getPaymentOptions();
    }

    /**
     * Adding ViaBill Price Tag Script Before Body Closing Tag.
     *
     * @return string|void
     *
     * @throws Exception
     */
    public function hookDisplayBeforeBodyClosingTag()
    {
        if (!$this->isPriceTagActive($this->context->controller->php_self)) {
            return;
        }

        /**
         * @var \ViaBill\Builder\Template\TagScriptTemplate $scriptTemplate
         */
        $scriptTemplate = $this->getModuleContainer()->get('builder.template.tagScript');
        $scriptTemplate->setSmarty($this->context->smarty);
        $scriptTemplate->setTagScript(Configuration::get(Config::API_TAGS_SCRIPT));

        return $scriptTemplate->getHtml();
    }

    /**
     * Adds Notifications To BO Order Page Header.
     * Checks If User Is Logged In.
     *
     * @throws Exception
     */
    public function callConditionallyNotificationService() // previously public function hookDisplayBackOfficeHeader()
    {
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $this->getModuleContainer()->get('config');

        if (!$config->isLoggedIn()) {
            return;
        }

        if (!$this->context->controller instanceof AdminDashboardController) {
            return;
        }

        $idOrder = Tools::getValue('id_order');
        if ($idOrder) {
            return;
        }

        // Add some randomness, you don't want to be executed all the times
        $freq_num = 15;
        mt_srand();
        $rand_id = mt_rand(1, $freq_num);
        if ($rand_id != 1) {
            return;
        }

        /** @var \ViaBill\Service\Api\Notification\NotificationService $notificationService */
        $notificationService = $this->getModuleContainer()->get('service.notification');
        $notifications = $notificationService->getNotifications();

        foreach ($notifications as $notification) {
            $this->context->controller->informations[] =
                sprintf('%s: %s', $this->displayName, $notification->getMessage());
        }
    }

    /**
     * Adds ViaBill Price Tag To Product Page Price Block.
     *
     * @param array $params
     *
     * @return string|void
     *
     * @throws Exception
     */
    public function hookDisplayProductPriceBlock($params)
    {
        if ($params['type'] !== 'after_price') {
            return;
        }

        if (!$this->isPriceTagActive($this->context->controller->php_self)) {
            return;
        }

        $product = $params['product'];

        /**
         * @var \ViaBill\Builder\Template\DynamicPriceTemplate $tagPriceTemplate
         */
        $tagPriceTemplate = $this->getModuleContainer()->get('builder.template.tagPriceHolder');
        $tagPriceTemplate->setSmarty($this->context->smarty);
        $tagPriceTemplate->setPrice($product['price_amount']);

        return $tagPriceTemplate->getHtml();
    }

    /**
     * Display content in shopping cart (Placeholder for hook validation).
     *
     * @param array $params
     *
     * @return string|void
     */
    public function hookDisplayShoppingCart($params)
    {
        // If you want to show the same price tag as in express checkout, you could reuse it:
        // return $this->hookDisplayExpressCheckout($params);

        return;
    }

    /**
     * Adds ViaBill Price Tag To Cart Page Price Block.
     *
     * @param array $params
     *
     * @return string|void
     *
     * @throws Exception
     */
    public function hookDisplayExpressCheckout($params)
    {
        if (!$this->isPriceTagActive($this->context->controller->php_self)) {
            return;
        }

        /**
         * @var Cart $cart
         */
        $cart = $params['cart'];
        /**
         * @var \ViaBill\Builder\Template\DynamicPriceTemplate $tagPriceTemplate
         */
        $tagPriceTemplate = $this->getModuleContainer()->get('builder.template.tagPriceHolder');
        $tagPriceTemplate->setSmarty($this->context->smarty);
        $tagPriceTemplate->setPrice($cart->getOrderTotal());

        return $tagPriceTemplate->getHtml();
    }

    /**
     * Method disallows to change order status if order status is "Payment pending by ViaBill"
     *
     * @param $params
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionOrderStatusUpdate($params)
    {
        if (!isset($this->context->employee) || !$this->context->employee->isLoggedBack()) {
            return true;
        }

        /** @var \ViaBill\Service\Validator\Payment\OrderValidator $orderPaymentValidator */
        $orderPaymentValidator = $this->getModuleContainer()->get('service.validator.payment.order');

        $order = new Order((int) $params['id_order']);

        $validationResult = $orderPaymentValidator->validateIsOrderWithModulePayment($order);

        if (!$validationResult->isValidationAccepted()) {
            return false;
        }

        $paymentInPendingState = Configuration::get(Config::PAYMENT_PENDING) &&
            $order->current_state === Configuration::get(Config::PAYMENT_PENDING);

        if (!$paymentInPendingState) {
            return true;
        }

        if ($this->context->controller instanceof ViaBillCallBackModuleFrontController) {
            return true;
        }

        $warnings = [
            $this->l('The status cannot be changed, since ViaBill has not yet approved the payment. Please try again later.'),
        ];

        /** @var \ViaBill\Service\MessageService $messageService */
        $messageService = $this->getModuleContainer()->get('service.message');
        $messageService->redirectWithMessages($order, [], [], $warnings);
    }

    /**
     * Adds auto full capture functionality when the status of the order is changed to "Payment completed by ViaBill"
     *
     * @param $params
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionOrderHistoryAddAfter($params)
    {
        /** @var \ViaBill\Service\Validator\Payment\OrderValidator $orderPaymentValidator */
        /** @var \ViaBill\Service\Order\OrderStatusService $orderStatusService */
        $orderPaymentValidator = $this->getModuleContainer()->get('service.validator.payment.order');
        $orderStatusService = $this->getModuleContainer()->get('service.order.orderStatus');

        $orderHistory = $params['order_history'];

        $order = new Order((int) $orderHistory->id_order);

        $validationResult = $orderPaymentValidator->validateIsOrderWithModulePayment($order);

        if (!$validationResult->isValidationAccepted()) {
            return false;
        }

        $debug_str = var_export($order, true);
        DebugLog::msg('hookActionOrderHistoryAddAfter / validation accepted for order: ' . $debug_str);

        $newOrderStatusId = (int) $orderHistory->id_order_state;
        $viaBillPaymentCompletedOrderStatus = (int) Configuration::get(Config::PAYMENT_COMPLETED);
        $enableAutoPaymentCapture = (bool) Configuration::get(Config::ENABLE_AUTO_PAYMENT_CAPTURE);
        $captureMultiselectOrderStatuses = $orderStatusService->getDecodedCaptureMultiselectOrderStatuses();

        if (($viaBillPaymentCompletedOrderStatus && $newOrderStatusId === $viaBillPaymentCompletedOrderStatus) ||
            ($enableAutoPaymentCapture && in_array($newOrderStatusId, $captureMultiselectOrderStatuses))
        ) {
            $this->capturePayment($order);
        }

        return false;
    }

    /**
     * @param array $params
     *
     * @return void|false
     */
    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        if (!Config::isVersionAbove177()) {
            return false;
        }

        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];
        foreach ($definition->getColumns() as $column) {
            if ($column->getId() !== 'actions') {
                continue;
            }

            $tmpActionColumn = [];
            foreach ($column->getOptions()['actions'] as $action) {
                $tmpActionColumn[] = $action;
                $optionTmp = $action->getOptions();
                $optionTmp['use_inline_display'] = false;
                $action->setOptions($optionTmp);
            }

            //Reverses row collection array for display correctly in admin order view page
            usort($tmpActionColumn, function (LinkRowAction $action) {
                return $action->getId() === 'view' ? -1 : 1;
            });

            $actionCollection = (new RowActionCollection());
            foreach ($tmpActionColumn as $action) {
                $actionCollection->add($action);
            }
            $column->setOptions(['actions' => $actionCollection]);
            $this->setActionColumnRows($column->getOptions()['actions']);
        }

        $definition->getBulkActions()
            ->add(
                (new SubmitBulkAction('capture_multiple_payments'))
                ->setName($this->l('Capture ViaBill payments'))
                ->setOptions(['submit_route' => 'capture_multiple_payments'])
            )
            ->add(
                (new SubmitBulkAction('cancel_multiple_payments'))
                ->setName($this->l('Cancel ViaBill payments'))
                ->setOptions(['submit_route' => 'cancel_multiple_payments'])
            )
            ->add(
                (new SubmitBulkAction('refund_multiple_payments'))
                ->setName($this->l('Refund ViaBill payments'))
                ->setOptions(['submit_route' => 'refund_multiple_payments'])
            );
    }

    /**
     * @param $action
     */
    private function setActionColumnRows($action)
    {
        $action->add(
            (new LinkRowAction('viabill_capture_single_payment'))
                ->setName($this->l('Capture ViaBill payment'))
                ->setIcon('attach_money')
                ->setOptions([
                    'route' => 'viabill_capture_single_payment',
                    'route_param_name' => 'orderId',
                    'route_param_field' => 'id_order',
                    'confirm_message' => $this->l('Would you like to capture payment?'),
                    'accessibility_checker' => $this->getModuleContainer()
                        ->get('grid.row.captureAccessibilityChecker'),
                ])
        )
            ->add(
                (new LinkRowAction('viabill_cancel_single_payment'))
                    ->setName($this->l('Cancel ViaBill payment'))
                    ->setIcon('money_off')
                    ->setOptions([
                        'route' => 'viabill_cancel_single_payment',
                        'route_param_name' => 'orderId',
                        'route_param_field' => 'id_order',
                        'confirm_message' => $this->l('Would you like to cancel payment?'),
                        'accessibility_checker' => $this->getModuleContainer()
                            ->get('grid.row.cancelAccessibilityChecker'),
                    ])
            )
            ->add(
                (new LinkRowAction('viabill_refund_single_payment'))
                    ->setName($this->l('Refund ViaBill payment'))
                    ->setIcon('bar_chart')
                    ->setOptions([
                        'route' => 'viabill_refund_single_payment',
                        'route_param_name' => 'orderId',
                        'route_param_field' => 'id_order',
                        'confirm_message' => $this->l('Would you like to refund payment?'),
                        'accessibility_checker' => $this->getModuleContainer()
                            ->get('grid.row.refundAccessibilityChecker'),
                    ])
            );
    }

    /**
     * Getting Order List Action Confirmation Message.
     *
     * @param string $type
     *
     * @return string
     */
    public function getConfirmationMessageTranslation($type)
    {
        $message = '';
        switch ($type) {
            case 'cancel':
                $message = $this->l('Are you sure that you want to cancel selected orders?');
                break;
            case 'capture':
                $message = $this->l('Are you sure that you want to capture selected orders?');
                break;
            case 'refund':
                $message = $this->l('Are you sure you want to refund these transactions?');
                break;
        }

        return $message;
    }

    /**
     * Getting Order Bulk Action Translation.
     *
     * @param string $type
     *
     * @return string
     */
    public function getBulkActionTranslation($type)
    {
        $message = '';
        switch ($type) {
            case 'cancel':
                $message = $this->l('Cancel payments');
                break;
            case 'capture':
                $message = $this->l('Capture payments');
                break;
            case 'refund':
                $message = $this->l('Refund payments');
                break;
        }

        return $message;
    }

    /**
     * Getting Order Single Action Translation.
     *
     * @param string $type
     *
     * @return string
     */
    public function getSingleActionTranslations($type)
    {
        $message = '';
        switch ($type) {
            case 'capture':
                $message = $this->l('Capture payment');
                break;
            case 'refund':
                $message = $this->l('Refund payment');
                break;
            case 'cancel':
                $message = $this->l('Cancel payment');
                break;
        }

        return $message;
    }

    /**
     * Getting Order Action Confirmation Message.
     *
     * @param string $type
     * @param Order $order
     * @param null $customAmount
     *
     * @return string
     */
    public function getConfirmationTranslation($type, Order $order, $customAmount = null)
    {
        $message = '';
        $currency = new Currency($order->id_currency, $this->context->language->id);

        switch ($type) {
            case 'refund':
                $amount = $order->total_paid_tax_incl;
                if ($customAmount) {
                    $amount = $customAmount;
                }
                $message =
                    sprintf(
                        $this->l('Are you sure that you want to refund %s ?'),
                        Tools::displayPrice($amount, $currency)
                    );
                break;
            case 'capture':
                $message =
                    sprintf(
                        $this->l('Are you sure that you want to capture %s ?'),
                        Tools::displayPrice($order->total_paid_tax_incl, $currency)
                    );
                break;
            case 'cancel':
                $message =
                    sprintf(
                        $this->l('Are you sure that you want to cancel order %s ?'),
                        $order->reference
                    );
                break;
        }

        return $message;
    }

    /**
     * Returns Symfony DI Container
     *
     * @return Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getModuleContainer()
    {
        return $this->moduleContainer;
    }

    /**
     * @param Order $order
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isViabillOrder(Order $order)
    {
        /**
         * @var \ViaBill\Config\Config $config
         */
        $config = $this->getModuleContainer()->get('config');

        $isModuleOrder = $order->module === $this->name;
        $isLogged = $config->isLoggedIn();

        if (!$isModuleOrder || !$isLogged) {
            return false;
        }

        return true;
    }

    /**
     * Captures payment
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function capturePayment(Order $order)
    {
        /** @var \ViaBill\Service\Provider\OrderStatusProvider $orderStatus */
        $orderStatus = $this->getModuleContainer()->get('service.provider.orderStatus');

        DebugLog::msg('capturePayment / called');
        $debug_str = 'Memory Usage:' . memory_get_usage() . ' Peak Usage: ' . memory_get_peak_usage();
        DebugLog::msg($debug_str);

        if (!$orderStatus->canBeCaptured($order)) {
            DebugLog::msg('capturePayment / Order cannot be captured:');
            $debug_str = var_export($order, true);
            DebugLog::msg($debug_str);

            return false;
        }

        $remainingToCapture = $orderStatus->getRemainingToCapture($order);

        DebugLog::msg("capturePayment / remaining to capture: $remainingToCapture");

        /** @var \ViaBill\Service\Handler\PaymentManagementHandler $paymentHandler */
        /** @var \ViaBill\Service\MessageService $messageService */
        $paymentHandler = $this->getModuleContainer()->get('service.handler.paymentManagement');
        $messageService = $this->getModuleContainer()->get('service.message');

        $handleResponse = $paymentHandler->handle(
            $order,
            false,
            true,
            false,
            false,
            $remainingToCapture
        );

        $errors = $handleResponse->getErrors();
        $warnings = $handleResponse->getWarnings();
        $confirmations = [];

        if (empty($errors) && $handleResponse->getSuccessMessage()) {
            DebugLog::msg('capturePayment / success: ' . $handleResponse->getSuccessMessage());
            $confirmations[] = $handleResponse->getSuccessMessage();
        }

        $messageService->setMessages($confirmations, $errors, $warnings);

        return true;
    }

    /**
     * Includes Vendor Autoload.
     */
    private function autoLoad()
    {
        require_once $this->getLocalPath() . 'vendor/autoload.php';
    }

    /**
     * Adds Cache To DI Container.
     *
     * @throws Exception
     */
    private function compile()
    {
        $containerCache = $this->getLocalPath() . 'var/cache/container.php';
        $containerConfigCache = new ConfigCache($containerCache, self::DISABLE_CACHE);
        $containerClass = get_class($this) . 'Container';
        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            $locator = new FileLocator($this->getLocalPath() . '/config');
            $loader = new YamlFileLoader($containerBuilder, $locator);
            $loader->load('config.yml');
            $containerBuilder->compile();
            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => $containerClass]),
                $containerBuilder->getResources()
            );
        }
        require_once $containerCache;
        $this->moduleContainer = new $containerClass();
    }

    /**
     * Init ViaBill Price Tag In Product Page.
     *
     * @param Media $mediaAdapter
     *
     * @throws Exception
     */
    private function initPriceTagsProductPage(Media $mediaAdapter)
    {
        /**
         * @var \ViaBill\Builder\Template\TagBodyTemplate $tagBodyTemplate
         */
        $tagBodyTemplate = $this->getModuleContainer()->get('builder.template.tagBody');

        $tagBodyTemplate->setView(Config::getTagsViewByController($this->context->controller->php_self));

        $tagBodyTemplate->setLanguage($this->context->language);
        $tagBodyTemplate->setCurrency($this->context->currency);
        $tagBodyTemplate->setSmarty($this->context->smarty);
        $tagBodyTemplate->setDynamicPriceSelector(
            Configuration::get(Config::DYNAMIC_PRICE_PRODUCT_SELECTOR));
        $tagBodyTemplate->useExtraGap(true);

        $tagBodyTemplate->setDynamicPriceTrigger(
            Configuration::get(Config::DYNAMIC_PRICE_PRODUCT_TRIGGER)
        );

        $this->context->smarty->assign($tagBodyTemplate->getSmartyParams());

        $mediaAdapter->addJsDef([
            'priceTagScriptHolder' => $tagBodyTemplate->getHtml(),
            'dynamicPriceTagTrigger' => Configuration::get(Config::DYNAMIC_PRICE_PRODUCT_TRIGGER),
        ]);

        $mediaAdapter->addJs($this->context, 'views/js/front/product_update_handler.js');
        $mediaAdapter->addCss($this->context, 'views/css/front/price-tag.css');
    }

    /**
     * Init ViaBill Price Tag In Cart Page.
     *
     * @param Media $mediaAdapter
     *
     * @throws Exception
     */
    private function initPriceTagsCartPage(Media $mediaAdapter)
    {
        /**
         * @var \ViaBill\Builder\Template\TagBodyTemplate $tagBodyTemplate
         */
        $tagBodyTemplate = $this->getModuleContainer()->get('builder.template.tagBody');

        $tagBodyTemplate->setSmarty($this->context->smarty);
        $tagBodyTemplate->setView(Config::getTagsViewByController($this->context->controller->php_self));
        $tagBodyTemplate->setCurrency($this->context->currency);
        $tagBodyTemplate->setLanguage($this->context->language);
        $tagBodyTemplate->setDynamicPriceSelector(
            Configuration::get(Config::DYNAMIC_PRICE_CART_SELECTOR));
        $tagBodyTemplate->setDynamicPriceTrigger(
            Configuration::get(Config::DYNAMIC_PRICE_CART_TRIGGER));
        $tagBodyTemplate->useColumns(true);
        $tagBodyTemplate->useExtraGap(true);

        $this->context->smarty->assign($tagBodyTemplate->getSmartyParams());
        $mediaAdapter->addJsDef([
            'priceTagCartBodyHolder' => $tagBodyTemplate->getHtml(),
            'dynamicPriceTagTrigger' => Configuration::get(Config::DYNAMIC_PRICE_CART_TRIGGER),
        ]);
        $mediaAdapter->addJs($this->context, 'views/js/front/cart_update_handler.js');
        $mediaAdapter->addCss($this->context, 'views/css/front/price-tag.css');
    }

    /**
     * Examine if you need to block the submission of an email when order status changes
     *
     * @param array $params
     *
     * @throws Exception
     */
    public function hookActionEmailSendBefore(&$params)
    {
        $db = Db::getInstance();

        $sendEmail = true;
        $order_id = null;
        $order_state = null;
        $order_module = null;

        $template = null;
        if (isset($params['template'])) {
            $template = $params['template'];
            if ($template == 'order_conf') {
                // try to load the order object
                if (isset($params['templateVars'])) {
                    $templateVars = $params['templateVars'];
                    if (isset($templateVars['{id_order}'])) {
                        $order_id = (int) $templateVars['{id_order}'];
                    } elseif (isset($templateVars['{order_name}'])) {
                        $sql = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders` WHERE `reference` = "' . $templateVars['{order_name}'] . '"';
                        $order_id = Db::getInstance()->getValue($sql);
                    }
                }
                if (empty($order_id)) {
                    if (isset($params['cart'])) {
                        if (is_object($params['cart'])) {
                            $cart = $params['cart'];
                            $cart_id = (int) $cart->id;
                            if (!empty($cart_id)) {
                                $order_id = Order::getOrderByCartId($cart_id);
                            }
                        }
                    }
                }

                if (!empty($order_id)) {
                    $order = new Order((int) ($order_id));
                    $order_state = $order->current_state;
                    $order_module = Tools::strtolower($order->module);

                    $lang_id = (isset($params['idLang'])) ? (int) $params['idLang'] : 0;
                    $subject = (isset($params['subject'])) ? $params['subject'] : '';

                    $paymentPendingState = Configuration::get(Config::PAYMENT_PENDING);
                    $paymentAcceptedState = Configuration::get(Config::PAYMENT_ACCEPTED);

                    if ($order_module == 'viabill') {
                        $sendEmail = false;

                        if ($order_state == $paymentPendingState) {
                            $template_vars = serialize($templateVars);
                            $date_created = date('Y-m-d H:i:s');

                            $db->insert('viabill_order_conf_mail', [
                                'order_id' => (int) $order_id,
                                'lang_id' => (int) $lang_id,
                                'subject' => pSQL($subject),
                                'template_vars' => pSQL($template_vars, true),
                                'date_created' => pSQL($date_created),
                            ]);
                        } elseif ($order_state == $paymentAcceptedState) {
                            $query = 'SELECT * FROM `' . _DB_PREFIX_ .
                                'viabill_order_conf_mail` WHERE order_id = ' . $order_id;

                            $row = $db->getRow($query);
                            if (!empty($row)) {
                                $sendEmail = true;

                                $params['subject'] = $row['subject'];

                                $u_params = unserialize($row['template_vars']);
                                foreach ($u_params as $key => $value) {
                                    if (!isset($templateVars[$key])) {
                                        $params['templateVars'][$key] = $value;
                                    }
                                }
                                $params['templateVars']['{payment}'] = $this->l('Payment accepted by ViaBill');

                                $db->delete('viabill_order_conf_mail', 'order_id = ' . (int) $order_id);
                            }
                        }
                    }
                }
            }
        }

        // sanity check, remove confirmation email messages for all orders that did not
        // receive a callback call
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-2 days'));
        $db->delete('viabill_order_conf_mail', 'date_created < "' . pSQL($cutoff_date) . '"');

        if ($sendEmail) {
            $debug_str = "** Action Hook Order #{$order_id} [SENT] state: $order_state module: $order_module";
            DebugLog::msg($debug_str);
        } else {
            $debug_str = "** Action Hook Order #{$order_id} [IGNORED] state: $order_state module: $order_module";
            DebugLog::msg($debug_str);
        }

        $debug_str = print_r($params, true);
        DebugLog::msg($debug_str);

        return $sendEmail;
    }

    /**
     * Loads assets for legacy order page (PS 1.7.3 - 1.7.6).
     * This replaces the override approach with JavaScript injection.
     */
    public function hookDisplayBackOfficeHeader()
    {
        // check if you need to retrieve notifications from ViaBill
        $this->callConditionallyNotificationService();

        // Only execute on AdminOrders controller
        if (Tools::getValue("controller") !== "AdminOrders") {
            return;
        }

        // Only for legacy versions (before 1.7.7)
        if (Config::isVersionAbove177()) {
            return;
        }

        /** @var \ViaBill\Adapter\Media $mediaAdapter */
        $mediaAdapter = $this->getModuleContainer()->get("adapter.media");

        // Add JavaScript for injecting buttons
        $mediaAdapter->addJsAdmin($this->context, "viabill-legacy-order-actions.js");

        // Add CSS for styling the buttons
        $mediaAdapter->addCssAdmin($this->context, "viabill-legacy-order-actions.css");
    }

    /**
     * Hook to inject custom CSS in the front-end header.
     */
    public function hookHeader()
    {
        try {
            $customCSS = Configuration::get("VIABILL_CUSTOM_CSS");
            
            if (!empty($customCSS)) {
                return 
                    '<style type="text/css">' . "\n" . 
                    $customCSS . "\n" . 
                    '</style>';
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('ViaBill Custom CSS error: ' . $e->getMessage(), 3);
        }
        
        return '';
    }

    /**
     * Hook to inject custom JavaScript in the front-end footer.
     */
    public function hookFooter($params)
    {
        try {
            $customJS = Configuration::get("VIABILL_CUSTOM_JS");
            
            if (!empty($customJS)) {
                return 
                    '<script type="text/javascript">' . "\n" . 
                    $customJS . "\n" . 
                    '</script>';
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('ViaBill Custom JS error: ' . $e->getMessage(), 3);
        }
        
        return '';
    }

}
