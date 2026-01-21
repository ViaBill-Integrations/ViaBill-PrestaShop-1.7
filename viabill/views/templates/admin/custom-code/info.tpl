{*
* ViaBill Custom Code Info Template
* Place this file in: viabill/views/templates/admin/custom-code/info.tpl
*}

<div class="viabill-custom-code-info">
    <h3>ℹ️ {l s='Custom CSS and JavaScript Code' mod='viabill'}</h3>
    <p>
        {l s='This feature allows you to add custom CSS and JavaScript code to enhance your ViaBill integration without modifying theme files.' mod='viabill'}
    </p>
    <p>
        <strong>{l s='Use cases:' mod='viabill'}</strong>
    </p>
    <ul>
        <li>{l s='Customize the appearance of ViaBill price tags' mod='viabill'}</li>
        <li>{l s='Add custom styling to payment buttons' mod='viabill'}</li>
        <li>{l s='Integrate third-party analytics or tracking scripts' mod='viabill'}</li>
        <li>{l s='Implement custom behavior for the checkout process' mod='viabill'}</li>
    </ul>
</div>

<div class="viabill-custom-code-warning">
    <h4>⚠️ {l s='Important Notes' mod='viabill'}</h4>
    <p>
        {l s='Do NOT include <style> or <script> tags in your code - they will be added automatically.' mod='viabill'}
        {l s='Always test your custom code thoroughly before deploying to production.' mod='viabill'}
        {l s='Invalid code may break your website functionality.' mod='viabill'}
    </p>
</div>

<div class="viabill-code-examples">
    <h4>📝 {l s='Code Examples' mod='viabill'}</h4>
    
    <div class="example-item">
        <h5>{l s='Example 1: Customize ViaBill Price Tag Colors' mod='viabill'}</h5>
        <p>{l s='Add this to the Custom CSS Code field:' mod='viabill'}</p>
        <pre>/* Customize ViaBill price tag appearance */
.viabill-pricetag {
    color: #9b26b7 !important;
    font-weight: bold;
}

.viabill-pricetag a {
    color: #25b9d7 !important;
    text-decoration: underline;
}</pre>
    </div>
    
    <div class="example-item">
        <h5>{l s='Example 2: Load ViaBill Price Tag Script Dynamically' mod='viabill'}</h5>
        <p>{l s='Add this to the Custom JavaScript Code field (replace YOUR_MERCHANT_ID):' mod='viabill'}</p>
        <pre>// Dynamically load ViaBill price tag script
(function() {
    var merchantID = 'YOUR_MERCHANT_ID';
    if (merchantID && merchantID !== 'YOUR_MERCHANT_ID') {
        var script = document.createElement('script');
        script.src = 'https://pricetag.viabill.com/script/' + merchantID;
        script.async = true;
        document.body.appendChild(script);
    }
})();</pre>
    </div>
    
    <div class="example-item">
        <h5>{l s='Example 3: Track ViaBill Payment Selection' mod='viabill'}</h5>
        <p>{l s='Add this to the Custom JavaScript Code field:' mod='viabill'}</p>
        <pre>// Track when ViaBill payment method is selected
$(document).ready(function() {
    $('input[name="payment-option"]').on('change', function() {
        if ($(this).data('module-name') === 'viabill') {
            console.log('ViaBill payment method selected');
            // Add your tracking code here
            // Example: gtag('event', 'viabill_selected', { ... });
        }
    });
});</pre>
    </div>
    
    <div class="example-item">
        <h5>{l s='Example 4: Add Custom Styling to Payment Button' mod='viabill'}</h5>
        <p>{l s='Add this to the Custom CSS Code field:' mod='viabill'}</p>
        <pre>/* Style ViaBill payment option */
.payment-option[data-module-name="viabill"] {
    border: 2px solid #9b26b7;
    background: #f9f0ff;
}

.payment-option[data-module-name="viabill"]:hover {
    background: #f0e0ff;
    box-shadow: 0 2px 8px rgba(155, 38, 183, 0.2);
}</pre>
    </div>
</div>
