{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

{if $renewFormGroup.isVisible}
    <form
            method="post"
            action="{$paymentManagement.formAction}"
            data-id_order="{$paymentManagement.orderId|intval}"
    >
        {include file='../info-block.tpl'
            infoBlockText={l s='It’s possible to call renew to extend the time for which the transaction is reserved. ViaBill guarantees that an approved transaction can be captured within 14 days. If the capture is about to happen after 14 days, the transaction must be renewed first.' mod='viabill'}
        }

        <button
                class="btn btn-default"
                type="submit"
                name="renewPayment"
        >
            <i class="icon icon-history"></i> {l s='Renew' mod='viabill'}
        </button>
    </form>
{/if}