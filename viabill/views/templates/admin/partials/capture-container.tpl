{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{if $captureFormGroup.isVisible}
    <div class="panel card">
        <div class="panel-heading card-header">
            <i class="icon icon-money"></i> {l s='Charge' mod='viabill'}
        </div>
        <div class="panel-body card-body">
            <form
                    method="post"
                    class="form-inline"
                    action="{$paymentManagement.formAction}"
                    data-id_order="{$paymentManagement.orderId|intval}"
            >
                <div class="form-group">
                    <div class="input-group fixed-width-xl">
                        <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]+([,\.][0-9]+)?"
                                name="capture_amount"
                                {if $captureFormGroup.remainingToCapture}
                                    value="{$captureFormGroup.remainingToCapture|floatval}"
                                {/if}
                                class="capture-amount form-control"
                        >
                        <div class="input-group-addon input-group-text">
                            {$paymentManagement.currencySign}
                        </div>
                    </div>
                </div>
                <input type="hidden" name="capturePayment" value="1">
                <button
                        type="submit"
                        class="btn btn-default ml-2"
                        name="capturePayment"
                        {if $captureFormGroup.captureConfirmation}
                            data-ajax_capture="true"
                        {/if}
                >
                    <i class="icon icon-money"></i> {l s='Capture' mod='viabill'}
                </button>
            </form>
        </div>
    </div>
{/if}