/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

$(document).ready(function () {
    appendPriceTagToExistingContainer();
    var $changeTrigger = $('#'+dynamicPriceTagTrigger);
    $changeTrigger.on('change', function () {
        // this is only the trigger which is being called by price tags after ajax calls
    });

    if (typeof prestashop !== 'undefined') {
        prestashop.on('updatedCart', function (params) {
            $changeTrigger.trigger('change');
            $changeTrigger.trigger('click');
        });
    }

    function appendPriceTagToExistingContainer() {
        if (typeof priceTagCartBodyHolder === 'undefined') {
            return;
        }

        $('.cart-detailed-totals').after(priceTagCartBodyHolder);
    }
});