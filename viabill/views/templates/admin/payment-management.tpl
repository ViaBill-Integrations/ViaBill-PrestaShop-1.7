{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

<div class="viabill-payment-management-container panel card col-lg-8 col-md-10 col-xs-12">
    <div class="panel-heading card-header">
        <i class="icon icon-credit-card"></i> {l s = 'ViaBill payment actions' mod='viabill'}
    </div>
    <div class="panel-body card-body">
        {if $paymentManagement.isCancelled || $paymentManagement.isFullRefund || $paymentManagement.currencyError}

            {$message = {l s='Payment is cancelled' mod='viabill'}}

            {if $paymentManagement.isFullRefund}
                {$message = {l s='Payment is refunded' mod='viabill'}}
            {/if}

            {if $paymentManagement.currencyError}
                {$message = $paymentManagement.currencyError}
            {/if}

            {include file='./partials/info-message.tpl' message=$message}
        {else}
            {include file='./partials/capture-container.tpl' captureFormGroup=$paymentManagement.captureFormGroup}

            {if $paymentManagement.refundFormGroup.isVisible || $paymentManagement.cancelFormGroup.isVisible}
                <div class="panel card">
                    <div class="panel-heading card-header">
                        <i class="icon icon-circle-arrow-down"></i> {l s='Return' mod='viabill'}
                    </div>
                    <div class="panel-body card-body">
                        {include file='./partials/refund-container.tpl' refundFormGroup=$paymentManagement.refundFormGroup}
                        {include file='./partials/cancel-container.tpl' cancelFormGroup=$paymentManagement.cancelFormGroup}
                    </div>
                </div>
            {/if}

            {include file='./partials/renew-container.tpl' renewFormGroup=$paymentManagement.renewFormGroup}
        {/if}
    </div>
</div>
<div class="clearfix">&nbsp;</div>

