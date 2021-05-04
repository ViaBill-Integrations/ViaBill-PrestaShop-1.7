{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{if $cancelFormGroup.isVisible}
    <form
            method="post"
            action="{$paymentManagement.formAction}"
            data-id_order="{$paymentManagement.orderId|intval}"
    >
        <button
                class="btn btn-default"
                type="submit"
                name="cancelPayment"
                {if $cancelFormGroup.cancelConfirmation} onclick="return confirm('{l s= 'Are you sure that you want to cancel this order?' mod='viabill'}');" {/if}
        >
            <i class="icon icon-remove"></i> {l s='Cancel order' mod='viabill'}
        </button>
    </form>
{/if}