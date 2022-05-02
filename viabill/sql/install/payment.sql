CREATE TABLE IF NOT EXISTS `PREFIX_viabill_order` (
 `id_viabill_order`  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
 `id_order`  INT(10) UNSIGNED NOT NULL,
 `id_currency` INT(10) UNSIGNED NOT NULL,
 PRIMARY KEY (`id_viabill_order`),
 CONSTRAINT `FK_VIABILL_ORDER_ID` FOREIGN KEY (`id_order`) REFERENCES `PREFIX_orders` (`id_order`) ON DELETE CASCADE,
 CONSTRAINT `FK_VIABILL_ORDER_ID_CURRENCY` FOREIGN KEY (`id_currency`) REFERENCES `PREFIX_currency` (`id_currency`) ON DELETE CASCADE
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_viabill_order_capture` (
  `id_viabill_order_capture`  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_order` INT(10) UNSIGNED NOT NULL,
  `amount` DECIMAL(20,6),
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_viabill_order_capture`),
  CONSTRAINT `FK_VIABILL_ORDER_CAPTURE_ORDER_ID` FOREIGN KEY (`id_order`) REFERENCES `PREFIX_orders` (`id_order`) ON DELETE CASCADE
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_viabill_order_refund` (
  `id_viabill_order_refund`  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_order` INT(10) UNSIGNED NOT NULL,
  `amount` DECIMAL(20,6),
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_viabill_order_refund`),
  CONSTRAINT `FK_VIABILL_ORDER_REFUND_ORDER_ID` FOREIGN KEY (`id_order`) REFERENCES `PREFIX_orders` (`id_order`) ON DELETE CASCADE
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_viabill_pending_order_cart` (
    `id_viabill_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT(64) NOT NULL,
    `cart_id` INT(64) NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_viabill_order_conf_mail` (
    `id_viabill_order_conf_mail`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT(64) NOT NULL,
    `lang_id` INT(32) NOT NULL,
    `subject` varchar(512) NOT NULL,
    `template_vars` text NOT NULL,
    `date_created` datetime NOT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_viabill_transaction_history` (
    `id_viabill_transaction_history`  INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `transaction_id` varchar(256) NOT NULL,
    `order_id` INT(64) NOT NULL,
    `checkout_out_date` datetime NOT NULL,
    `checkout_out_params` varchar(2048) NOT NULL,
    `checkout_out_response` varchar(2048) NOT NULL,
    `checkout_out_success` TINYINT(1) NOT NULL,
    `callback_in_date` datetime DEFAULT NULL,
    `callback_in_params` varchar(2048) DEFAULT NULL,
    `callback_in_status` varchar(32) DEFAULT NULL,
    `complete_in_date` datetime DEFAULT NULL,
    `complete_in_approved` TINYINT(1) NOT NULL,
    `cancel_in_date` datetime DEFAULT NULL,
    `cancel_in_params` varchar(2048) DEFAULT NULL,
    `notes` text DEFAULT NULL
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
