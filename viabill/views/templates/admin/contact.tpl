{**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*}

<div class="container" style="margin-top: 20px;margin-bottom: 20px;">
    <h3>Support Request Form</h3>
    <div class="alert alert-info" role="alert">
        {l s='Please fill out the form below and click on the ' mod='viabill'}
        <em>{l s='Send Support Request' mod='viabill'}</em> 
        {l s='button to send your request.' mod='viabill'}
    </div>
    <form id="tech_support_form" action="{$action_url}" method="post">
    <fieldset>
        <legend class="w-auto text-primary">{l s='Issue Description' mod='viabill'}</legend>
        <div class="form-group">
            <label>{l s='Your Name' mod='viabill'}</label>
            <input class="form-control" type="text" required="true" name="ticket_info[name]"
                 value="" />
        </div>
        <div class="form-group">
            <label>{l s='Your Email' mod='viabill'}</label>
            <input class="form-control" type="text" required="true" name="ticket_info[email]"
                 value="" />
        </div>
        <div class="form-group">
            <label>{l s='Message' mod='viabill'}</label>
            <textarea class="form-control" name="ticket_info[issue]" 
            placeholder="Type your issue description here ..." rows="10" required="true"></textarea>
        </div>
    </fieldset>
    <fieldset>
        <legend class="w-auto text-primary">Eshop Info</legend>
        <div class="form-group">
            <label>{l s='Store Name' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
                 value="{$storeName}" name="shop_info[name]" />
        </div>                
        <div class="form-group">
            <label>{l s='Store URL' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
             value="{$storeURL}" name="shop_info[url]" />
        </div>
        <div class="form-group">
            <label>{l s='Store Email' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
             value="{$storeEmail}" name="shop_info[email]" />
        </div>
        <div class="form-group">
            <label>{l s='Eshop Country' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
             value="{$storeCountry}" name="shop_info[country]" />
        </div>
        <div class="form-group">
            <label>{l s='Eshop Language' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
             value="{$langCode}" name="shop_info[language]" />
        </div>
        <div class="form-group">
            <label>{l s='Eshop Currency' mod='viabill'}</label>
            <input class="form-control" type="text" required="true"
             value="{$currencyCode}" name="shop_info[currency]" />
        </div>                
        <div class="form-group">
            <label>{l s='Module Version' mod='viabill'}</label>
            <input class="form-control" type="text"
             value="{$module_version}" name="shop_info[addon_version]" />
        </div>
        <div class="form-group">
            <label>{l s='PrestaShop Version' mod='viabill'}</label>
            <input type="hidden" value="prestashop" name="shop_info[platform]" />
            <input class="form-control" type="text"
             value="{$prestashop_version}" name="shop_info[platform_version]" />
        </div>
        <div class="form-group">
            <label>{l s='PHP Version' mod='viabill'}</label>
            <input class="form-control" type="text"
             value="{$php_version}" name="shop_info[php_version]" />
        </div>
        <div class="form-group">
            <label>{l s='Memory Limit' mod='viabill'}</label>
            <input class="form-control" type="text"
             value="{$memory_limit}" name="shop_info[memory_limit]" />
        </div>
        <div class="form-group">
            <label>{l s='O/S' mod='viabill'}</label>
            <input class="form-control" type="text"
             value="{$os}" name="shop_info[os]" />
        </div>
        <div class="form-group">
            <label>{l s='Debug File' mod='viabill'}</label>
            <input class="form-control" type="text"
             value="{$debug_file}" name="shop_info[debug_file]" />
        </div>
        <div class="form-group">
            <label>{l s='Debug Data' mod='viabill'}</label>
            <textarea class="form-control"
             name="shop_info[debug_data]">{$debug_log_entries|escape:"html":'UTF-8'}</textarea>             
        </div>        
    </fieldset>            
    <div class="form-group form-check">
        <input type="checkbox" value="accepted" required="true"
         class="form-check-input" name="terms_of_use" id="terms_of_use"/>
          <label class="form-check-label">I have read and accept the
           <a href="{$terms_of_use_url}">Terms and Conditions</a></label>
    </div>       
    <input type="hidden" name="token" value="{$token}" />     
    <button type="button" onclick="validateAndSubmit()" class="btn btn-primary">
        {l s='Send Support Request' mod='viabill'}</button>    
    </form>
    </div>
    <script>
    function validateAndSubmit() {
        var form_id = "tech_support_form";
        var error_msg = "";
        var valid = true;
        
        jQuery("#" + form_id).find("select, textarea, input").each(function() {
            if (jQuery(this).prop("required")) {
                if (!jQuery(this).val()) {
                    valid = false;
                    var label = jQuery(this).closest(".form-group").find("label").text();
                    error_msg += "* " + label + " {l s='is required' mod='viabill'}\n";
                }
            }
        });
        
        if (jQuery("#terms_of_use").prop("checked") == false) {
            valid = false;
            error_msg += "* {l s='You need to accept The Terms and Conditions.' mod='viabill'}\n";
        }
        
        if (valid) {
            jQuery("#" + form_id).submit();	
        } else {
            error_msg = "{l s='Please correct the following errors and try again:' mod='viabill'}\n" + error_msg;
            alert(error_msg);
        }		
    }
    </script>