<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'ViaBill\\Adapter\\Configuration' => $baseDir . '/src/Adapter/Configuration.php',
    'ViaBill\\Adapter\\Context' => $baseDir . '/src/Adapter/Context.php',
    'ViaBill\\Adapter\\Currency' => $baseDir . '/src/Adapter/Currency.php',
    'ViaBill\\Adapter\\Media' => $baseDir . '/src/Adapter/Media.php',
    'ViaBill\\Adapter\\Order' => $baseDir . '/src/Adapter/Order.php',
    'ViaBill\\Adapter\\Tools' => $baseDir . '/src/Adapter/Tools.php',
    'ViaBill\\Adapter\\Validate' => $baseDir . '/src/Adapter/Validate.php',
    'ViaBill\\Builder\\Message\\MessageBuilderInterface' => $baseDir . '/src/Builder/Message/MessageBuilderInterface.php',
    'ViaBill\\Builder\\Message\\OrderMessageBuilder' => $baseDir . '/src/Builder/Message/OrderMessageBuilder.php',
    'ViaBill\\Builder\\Payment\\PaymentOptionsBuilder' => $baseDir . '/src/Builder/Payment/PaymentOptionsBuilder.php',
    'ViaBill\\Builder\\Template\\AuthenticationTemplate' => $baseDir . '/src/Builder/Template/AuthenticationTemplate.php',    
    'ViaBill\\Builder\\Template\\ContactTemplate' => $baseDir . '/src/Builder/Template/ContactTemplate.php',
    'ViaBill\\Builder\\Template\\DynamicPriceTemplate' => $baseDir . '/src/Builder/Template/DynamicPriceTemplate.php',
    'ViaBill\\Builder\\Template\\InfoBlockTemplate' => $baseDir . '/src/Builder/Template/InfoBlockTemplate.php',
    'ViaBill\\Builder\\Template\\ListButtonTemplate' => $baseDir . '/src/Builder/Template/ListButtonTemplate.php',
    'ViaBill\\Builder\\Template\\PaymentManagementTemplate' => $baseDir . '/src/Builder/Template/PaymentManagementTemplate.php',
    'ViaBill\\Builder\\Template\\TagBodyTemplate' => $baseDir . '/src/Builder/Template/TagBodyTemplate.php',
    'ViaBill\\Builder\\Template\\TagScriptTemplate' => $baseDir . '/src/Builder/Template/TagScriptTemplate.php',
    'ViaBill\\Builder\\Template\\TemplateInterface' => $baseDir . '/src/Builder/Template/TemplateInterface.php',
    'ViaBill\\Builder\\Template\\TermsAndConditionsTemplate' => $baseDir . '/src/Builder/Template/TermsAndConditionsTemplate.php',
    'ViaBill\\Config\\Config' => $baseDir . '/src/Config/Config.php',
    'ViaBill\\Controller\\AbstractAdminController' => $baseDir . '/src/Controller/AbstractAdminController.php',
    'ViaBill\\Controller\\OrderListActionController' => $baseDir . '/src/Controller/OrderListActionController.php',
    'ViaBill\\Factory\\HttpClientFactory' => $baseDir . '/src/Factory/HttpClientFactory.php',
    'ViaBill\\Factory\\LoggerFactory' => $baseDir . '/src/Factory/LoggerFactory.php',
    'ViaBill\\Factory\\RequestFactory' => $baseDir . '/src/Factory/RequestFactory.php',
    'ViaBill\\Factory\\SerializerFactory' => $baseDir . '/src/Factory/SerializerFactory.php',
    'ViaBill\\Grid\\Row\\CancelAccessibilityChecker' => $baseDir . '/src/Grid/Row/CancelAccessibilityChecker.php',
    'ViaBill\\Grid\\Row\\CaptureAccessibilityChecker' => $baseDir . '/src/Grid/Row/CaptureAccessibilityChecker.php',
    'ViaBill\\Grid\\Row\\RefundAccessibilityChecker' => $baseDir . '/src/Grid/Row/RefundAccessibilityChecker.php',
    'ViaBill\\Install\\AbstractInstaller' => $baseDir . '/src/Install/AbstractInstaller.php',
    'ViaBill\\Install\\Installer' => $baseDir . '/src/Install/Installer.php',
    'ViaBill\\Install\\Tab' => $baseDir . '/src/Install/Tab.php',
    'ViaBill\\Install\\UnInstaller' => $baseDir . '/src/Install/UnInstaller.php',
    'ViaBill\\Object\\Api\\ApiResponse' => $baseDir . '/src/Object/Api/ApiResponse.php',
    'ViaBill\\Object\\Api\\ApiResponseError' => $baseDir . '/src/Object/Api/ApiResponseError.php',
    'ViaBill\\Object\\Api\\Authentication\\LoginRequest' => $baseDir . '/src/Object/Api/Authentication/LoginRequest.php',
    'ViaBill\\Object\\Api\\Authentication\\LoginResponse' => $baseDir . '/src/Object/Api/Authentication/LoginResponse.php',
    'ViaBill\\Object\\Api\\Authentication\\RegisterRequest' => $baseDir . '/src/Object/Api/Authentication/RegisterRequest.php',
    'ViaBill\\Object\\Api\\Authentication\\RegisterResponse' => $baseDir . '/src/Object/Api/Authentication/RegisterResponse.php',
    'ViaBill\\Object\\Api\\CallBack\\CallBackResponse' => $baseDir . '/src/Object/Api/CallBack/CallBackResponse.php',
    'ViaBill\\Object\\Api\\Cancel\\CancelRequest' => $baseDir . '/src/Object/Api/Cancel/CancelRequest.php',
    'ViaBill\\Object\\Api\\Capture\\CaptureRequest' => $baseDir . '/src/Object/Api/Capture/CaptureRequest.php',
    'ViaBill\\Object\\Api\\Countries\\CountryResponse' => $baseDir . '/src/Object/Api/Countries/CountryResponse.php',
    'ViaBill\\Object\\Api\\Link\\LinkRequest' => $baseDir . '/src/Object/Api/Link/LinkRequest.php',
    'ViaBill\\Object\\Api\\Link\\LinkResponse' => $baseDir . '/src/Object/Api/Link/LinkResponse.php',
    'ViaBill\\Object\\Api\\Locale\\Locale' => $baseDir . '/src/Object/Api/Locale/Locale.php',
    'ViaBill\\Object\\Api\\Notification\\NotificationResponse' => $baseDir . '/src/Object/Api/Notification/NotificationResponse.php',
    'ViaBill\\Object\\Api\\ObjectResponseInterface' => $baseDir . '/src/Object/Api/ObjectResponseInterface.php',
    'ViaBill\\Object\\Api\\Payment\\PaymentRequest' => $baseDir . '/src/Object/Api/Payment/PaymentRequest.php',
    'ViaBill\\Object\\Api\\Payment\\PaymentResponse' => $baseDir . '/src/Object/Api/Payment/PaymentResponse.php',
    'ViaBill\\Object\\Api\\Refund\\RefundRequest' => $baseDir . '/src/Object/Api/Refund/RefundRequest.php',
    'ViaBill\\Object\\Api\\Renew\\RenewRequest' => $baseDir . '/src/Object/Api/Renew/RenewRequest.php',
    'ViaBill\\Object\\Api\\SerializedObjectInterface' => $baseDir . '/src/Object/Api/SerializedObjectInterface.php',
    'ViaBill\\Object\\Api\\Status\\StatusRequest' => $baseDir . '/src/Object/Api/Status/StatusRequest.php',
    'ViaBill\\Object\\Api\\Status\\StatusResponse' => $baseDir . '/src/Object/Api/Status/StatusResponse.php',
    'ViaBill\\Object\\Handler\\HandlerResponse' => $baseDir . '/src/Object/Handler/HandlerResponse.php',
    'ViaBill\\Object\\Validator\\ValidationResponse' => $baseDir . '/src/Object/Validator/ValidationResponse.php',
    'ViaBill\\Object\\ViaBillUser' => $baseDir . '/src/Object/ViaBillUser.php',
    'ViaBill\\Repository\\AbstractRepository' => $baseDir . '/src/Repository/AbstractRepository.php',
    'ViaBill\\Repository\\PendingOrderCartRepository' => $baseDir . '/src/Repository/PendingOrderCartRepository.php',
    'ViaBill\\Repository\\ReadOnlyRepositoryInterface' => $baseDir . '/src/Repository/ReadOnlyRepositoryInterface.php',
    'ViaBill\\Service\\Api\\ApiRequest' => $baseDir . '/src/Service/Api/ApiRequest.php',
    'ViaBill\\Service\\Api\\Authentication\\LoginService' => $baseDir . '/src/Service/Api/Authentication/LoginService.php',
    'ViaBill\\Service\\Api\\Authentication\\RegisterService' => $baseDir . '/src/Service/Api/Authentication/RegisterService.php',
    'ViaBill\\Service\\Api\\Cancel\\CancelService' => $baseDir . '/src/Service/Api/Cancel/CancelService.php',
    'ViaBill\\Service\\Api\\Capture\\CaptureService' => $baseDir . '/src/Service/Api/Capture/CaptureService.php',
    'ViaBill\\Service\\Api\\Countries\\CountryService' => $baseDir . '/src/Service/Api/Countries/CountryService.php',
    'ViaBill\\Service\\Api\\Link\\LinkService' => $baseDir . '/src/Service/Api/Link/LinkService.php',
    'ViaBill\\Service\\Api\\Locale\\LocaleService' => $baseDir . '/src/Service/Api/Locale/LocaleService.php',
    'ViaBill\\Service\\Api\\Notification\\NotificationService' => $baseDir . '/src/Service/Api/Notification/NotificationService.php',
    'ViaBill\\Service\\Api\\OrderStatusApiService' => $baseDir . '/src/Service/Api/OrderStatusApiService.php',
    'ViaBill\\Service\\Api\\Payment\\PaymentService' => $baseDir . '/src/Service/Api/Payment/PaymentService.php',
    'ViaBill\\Service\\Api\\Refund\\RefundService' => $baseDir . '/src/Service/Api/Refund/RefundService.php',
    'ViaBill\\Service\\Api\\Renew\\RenewService' => $baseDir . '/src/Service/Api/Renew/RenewService.php',
    'ViaBill\\Service\\Api\\Status\\StatusService' => $baseDir . '/src/Service/Api/Status/StatusService.php',
    'ViaBill\\Service\\Cart\\CartDuplicationService' => $baseDir . '/src/Service/Cart/CartDuplicationService.php',
    'ViaBill\\Service\\Cart\\MemorizeCartService' => $baseDir . '/src/Service/Cart/MemorizeCartService.php',
    'ViaBill\\Service\\Cart\\OrderCartAssociationService' => $baseDir . '/src/Service/Cart/OrderCartAssociationService.php',
    'ViaBill\\Service\\Handler\\CancelPaymentHandler' => $baseDir . '/src/Service/Handler/CancelPaymentHandler.php',
    'ViaBill\\Service\\Handler\\CapturePaymentHandler' => $baseDir . '/src/Service/Handler/CapturePaymentHandler.php',
    'ViaBill\\Service\\Handler\\ModuleRestrictionHandler' => $baseDir . '/src/Service/Handler/ModuleRestrictionHandler.php',
    'ViaBill\\Service\\Handler\\PaymentManagementHandler' => $baseDir . '/src/Service/Handler/PaymentManagementHandler.php',
    'ViaBill\\Service\\Handler\\RefundPaymentHandler' => $baseDir . '/src/Service/Handler/RefundPaymentHandler.php',
    'ViaBill\\Service\\Handler\\RenewPaymentHandler' => $baseDir . '/src/Service/Handler/RenewPaymentHandler.php',
    'ViaBill\\Service\\MessageService' => $baseDir . '/src/Service/MessageService.php',
    'ViaBill\\Service\\Order\\CreateOrderService' => $baseDir . '/src/Service/Order/CreateOrderService.php',
    'ViaBill\\Service\\Order\\OrderListActionsService' => $baseDir . '/src/Service/Order/OrderListActionsService.php',
    'ViaBill\\Service\\Order\\OrderStatusService' => $baseDir . '/src/Service/Order/OrderStatusService.php',
    'ViaBill\\Service\\Provider\\OrderStatusProvider' => $baseDir . '/src/Service/Provider/OrderStatusProvider.php',
    'ViaBill\\Service\\UserService' => $baseDir . '/src/Service/UserService.php',
    'ViaBill\\Service\\Validator\\CallBack\\OrderCallBackValidator' => $baseDir . '/src/Service/Validator/CallBack/OrderCallBackValidator.php',
    'ViaBill\\Service\\Validator\\LocaleValidator' => $baseDir . '/src/Service/Validator/LocaleValidator.php',
    'ViaBill\\Service\\Validator\\Payment\\CartValidator' => $baseDir . '/src/Service/Validator/Payment/CartValidator.php',
    'ViaBill\\Service\\Validator\\Payment\\CurrencyValidator' => $baseDir . '/src/Service/Validator/Payment/CurrencyValidator.php',
    'ViaBill\\Service\\Validator\\Payment\\OrderValidator' => $baseDir . '/src/Service/Validator/Payment/OrderValidator.php',
    'ViaBill\\Service\\Validator\\Payment\\PaymentValidator' => $baseDir . '/src/Service/Validator/Payment/PaymentValidator.php',
    'ViaBill\\Util\\LinksGenerator' => $baseDir . '/src/Util/LinksGenerator.php',
    'ViaBill\\Util\\NumberUtility' => $baseDir . '/src/Util/NumberUtility.php',
    'ViaBill\\Util\\DebugLog' => $baseDir . '/src/Util/DebugLog.php',
    'ViaBill\\Util\\SignaturesGenerator' => $baseDir . '/src/Util/SignaturesGenerator.php',
    'ViaBillOrder' => $baseDir . '/src/Entity/ViaBillOrder.php',
    'ViaBillOrderCapture' => $baseDir . '/src/Entity/ViaBillOrderCapture.php',
    'ViaBillOrderRefund' => $baseDir . '/src/Entity/ViaBillOrderRefund.php',
    'ViaBillPendingOrderCart' => $baseDir . '/src/Entity/ViaBillPendingOrderCart.php',
    'ViaBillTransactionHistory' => $baseDir . '/src/Entity/ViaBillTransactionHistory.php',
);
