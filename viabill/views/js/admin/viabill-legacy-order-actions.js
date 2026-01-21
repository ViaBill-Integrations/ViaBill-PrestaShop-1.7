/**
 * ViaBill Legacy Order Actions
 * JavaScript fallback for PrestaShop 1.7.0 - 1.7.6
 * 
 * This script injects ViaBill payment action buttons into the legacy order list
 * when the modern Grid component is not available.
 */

$(document).ready(function() {
    
    // Check if we're on the order list page
    if (typeof prestashop === 'undefined' || !$('.table-orders').length) {
        return;
    }

    /**
     * Add row actions (single order buttons) to ViaBill orders
     */
    function addViaBillRowActions() {
        $('.table-orders tbody tr').each(function() {
            var $row = $(this);
            var orderId = $row.find('td:first').text().trim();
            var paymentMethod = $row.find('td.payment').text().trim().toLowerCase();
            
            // Only add buttons for ViaBill orders
            if (paymentMethod !== 'viabill') {
                return;
            }
            
            // Find the actions column
            var $actionsCell = $row.find('td.actions');
            if (!$actionsCell.length) {
                $actionsCell = $row.find('td:last');
            }
            
            // Check if buttons already exist
            if ($actionsCell.find('.viabill-action-btn').length > 0) {
                return;
            }
            
            // Get the admin token
            var token = prestashop.token || '';
            
            // Build action URLs
            var captureUrl = 'index.php?controller=AdminViaBillActions&id_order=' + orderId + 
                           '&capturePayment=1&token=' + token;
            var cancelUrl = 'index.php?controller=AdminViaBillActions&id_order=' + orderId + 
                          '&cancelPayment=1&token=' + token;
            var refundUrl = 'index.php?controller=AdminViaBillActions&id_order=' + orderId + 
                          '&refundPayment=1&token=' + token;
            
            // Create button HTML
            var buttonsHtml = 
                '<div class="btn-group-action viabill-actions">' +
                '  <div class="btn-group">' +
                '    <a class="btn btn-default viabill-action-btn" href="' + captureUrl + '" ' +
                '       title="Capture ViaBill payment">' +
                '      <i class="icon-money"></i> Capture' +
                '    </a>' +
                '    <a class="btn btn-default viabill-action-btn" href="' + cancelUrl + '" ' +
                '       title="Cancel ViaBill payment">' +
                '      <i class="icon-remove"></i> Cancel' +
                '    </a>' +
                '    <a class="btn btn-default viabill-action-btn" href="' + refundUrl + '" ' +
                '       title="Refund ViaBill payment">' +
                '      <i class="icon-undo"></i> Refund' +
                '    </a>' +
                '  </div>' +
                '</div>';
            
            // Append buttons to actions cell
            $actionsCell.append(buttonsHtml);
        });
    }
    
    /**
     * Add bulk actions to the bulk actions dropdown
     */
    function addViaBillBulkActions() {
        var $bulkActionsSelect = $('select[name="submitBulkActions"]');
        
        if (!$bulkActionsSelect.length) {
            return;
        }
        
        // Check if bulk actions already added
        if ($bulkActionsSelect.find('option[value="captureViaBillPayment"]').length > 0) {
            return;
        }
        
        // Add a separator
        $bulkActionsSelect.append('<option disabled>──────────</option>');
        
        // Add ViaBill bulk actions
        $bulkActionsSelect.append(
            '<option value="captureViaBillPayment">Capture ViaBill payments</option>'
        );
        $bulkActionsSelect.append(
            '<option value="cancelViaBillPayment">Cancel ViaBill payments</option>'
        );
        $bulkActionsSelect.append(
            '<option value="refundViaBillPayment">Refund ViaBill payments</option>'
        );
    }
    
    /**
     * Handle bulk action form submission
     */
    function handleBulkActionSubmit() {
        var $form = $('.table-orders').closest('form');
        
        if (!$form.length) {
            return;
        }
        
        $form.on('submit', function(e) {
            var selectedAction = $('select[name="submitBulkActions"]').val();
            
            // Check if it's a ViaBill bulk action
            if (selectedAction && selectedAction.indexOf('ViaBillPayment') !== -1) {
                // Get selected order IDs
                var orderIds = [];
                $('input[name="orderBox[]"]:checked').each(function() {
                    orderIds.push($(this).val());
                });
                
                if (orderIds.length === 0) {
                    alert('Please select at least one order.');
                    e.preventDefault();
                    return false;
                }
                
                // Confirm action
                var actionName = selectedAction.replace('ViaBillPayment', '').toLowerCase();
                var confirmMessage = 'Are you sure you want to ' + actionName + ' ' + 
                                   orderIds.length + ' ViaBill payment(s)?';
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
                
                // Change form action to submit to ViaBill controller
                var token = prestashop.token || '';
                var actionUrl = 'index.php?controller=AdminViaBillActions&' + 
                              selectedAction + '=1&token=' + token;
                
                // Add order IDs as hidden inputs
                orderIds.forEach(function(orderId) {
                    $form.append('<input type="hidden" name="orderBox[]" value="' + orderId + '">');
                });
                
                $form.attr('action', actionUrl);
            }
        });
    }
    
    // Initialize
    addViaBillRowActions();
    addViaBillBulkActions();
    handleBulkActionSubmit();
    
    // Re-run when table is updated (for AJAX pagination, etc.)
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            addViaBillRowActions();
        });
        
        var targetNode = document.querySelector('.table-orders');
        if (targetNode) {
            observer.observe(targetNode, { childList: true, subtree: true });
        }
    }
});
