{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

<div>
  <select class="chosen searchable-multiselect" name="order_status_multiselect[]" multiple>
      {foreach $multiselectOrderStatuses as $orderStatus}
        <option value="{$orderStatus['id_order_state']}"{if $orderStatus['selected']} selected="selected"{/if}>
            {$orderStatus['name']}
        </option>
      {/foreach}
  </select>
</div>
<div>
  <p class="help-block">{l s='Start typing to see suggestions' mod='viabill'}</p>
</div>