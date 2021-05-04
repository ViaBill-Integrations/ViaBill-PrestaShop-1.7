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
    $(document).on('click', 'button[name="capturePayment"]', confirmationMessageEvent);
    $(document).on('click', 'button[name="refundPayment"]', refundMessageEvent);
    $(document).on('click', '.viabill-list-button', listButtonEvent);

    function confirmationMessageEvent(event) {
        var $button = $(this);

        if (!isAlertActive($button)) {
            return;
        }

        var $form = $button.closest('form');
        var idOrder = parseInt($form.data('id_order'));
        var actionUrl = $form.attr('action');
        var amount = parseFloat($form.find('.capture-amount').val());

        processSendAmountMessageRequest(event, $form, $button, idOrder, actionUrl, amount, 'capture');
    }

    function refundMessageEvent() {
        var $button = $(this);

        if (!isAlertActive($button)) {
            return;
        }

        var $form = $button.closest('form');
        var idOrder = parseInt($form.data('id_order'));
        var actionUrl = $form.attr('action');
        var amount = parseFloat(preformatFloat($form.find('.capture-amount').val()));

        processSendAmountMessageRequest(event, $form, $button, idOrder, actionUrl, amount, 'refund');
    }

    function listButtonEvent() {
        var confirmMessage = $(this).data('confirm-message');
        if (confirmMessage) {
            return confirm(confirmMessage);
        }
    }

    function processSendAmountMessageRequest(event, $form, $button, idOrder, actionUrl, amount, type) {
        if(!$form[0].checkValidity()){
            return false;
        }

        event.preventDefault();
        if (typeof  amount === 'undefined' || !amount) {
            $form.submit();
        }

        $.ajax({
            'url' : actionUrl,
            'method': 'POST',
            'data': {
                ajax: 1,
                idOrder: idOrder,
                amount: amount,
                action: 'displayMessage',
                type: type
            },
            'success': function (response) {
                var confirmResult = confirm(response);

                if (!confirmResult) {
                    event.preventDefault();
                } else {
                    $form.submit();
                }
            }
        });
    }

    function isAlertActive($button) {
        var isAlert = $button.data('ajax_capture');

        if (typeof isAlert === 'undefined' || !isAlert) {
            return false;
        }

        return true;
    }
});

function preformatFloat(float) {
    const commaPosition = float.indexOf(',');

    if(commaPosition === -1){
        return float;
    }

    const dotPosition = float.indexOf('.');

    if(dotPosition === -1){
        return float.replace(/\,/g, '.');
    }

    return ((commaPosition < dotPosition) ? (float.replace(/\,/g,'')) : (float.replace(/\./g,'').replace(',', '.')));
}
