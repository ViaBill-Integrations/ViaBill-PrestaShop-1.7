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
    updateProductVariantsEventHandler();

    function updateProductVariantsEventHandler() {
        var $changeTrigger = $('#'+dynamicPriceTagTrigger);
        $changeTrigger.on('change', function () {
            // this is only the trigger which is being called by price tags after ajax calls
        });

        if (typeof prestashop === 'undefined') {
            return;
        }

        prestashop.on('updatedProduct', function (params) {
            $changeTrigger.trigger('change');
            $changeTrigger.trigger('click');
        })
    }

    function appendPriceTagToExistingContainer() {
        if (typeof priceTagScriptHolder === 'undefined') {
            return;
        }

        $('.product-prices').after(priceTagScriptHolder);
    }
});

