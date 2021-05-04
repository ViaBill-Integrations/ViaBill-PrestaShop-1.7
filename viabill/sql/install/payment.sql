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