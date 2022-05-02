jQuery(document).ready(function() {
	recent_transactions_table = jQuery('#recent_transactions').DataTable( 
		{		
			"ordering": false,
			"responsive": true
		}
	);

	// input text filters
	jQuery( 'input.transfilter').on( 'keyup change', function () {
		var el_id = jQuery(this).attr('id');
		var column_id = parseInt(el_id.substr(7));			
		if ( recent_transactions_table.column(column_id).search() !== this.value ) {
			recent_transactions_table.column(column_id).search( this.value ).draw();
		}		
	});		
	
	// select text filters
	jQuery( 'select.transfilter').on( 'change', function () {
		var el_id = jQuery(this).attr('id');
		var column_id = parseInt(el_id.substr(7));			
		if ( recent_transactions_table.column(column_id).search() !== this.value ) {
			recent_transactions_table.column(column_id).search( this.value ).draw();
		}		
	});
	
	var status_column_id = 3;
	var status_select_options = recent_transactions_table
        .columns( status_column_id )
        .data()
        .eq( 0 )      // Reduce the 2D array into a 1D array of data
        .sort()       // Sort data alphabetically
        .unique();
		
	for (var i=0; i<status_select_options.length; i++) {
		jQuery('#filter_3').append($('<option>', {
			value: status_select_options[i],
			text: status_select_options[i]
		}));
	}
	
	var irregular_column_id = 4;
	var irregular_select_options = recent_transactions_table
        .columns( irregular_column_id )
        .data()
        .eq( 0 )      // Reduce the 2D array into a 1D array of data
        .sort()       // Sort data alphabetically
        .unique();			
	
} );

function toggleTransDetails(transaction_id) {
	jQuery('#details_'+transaction_id).toggle();
	var label_el = jQuery('#details_toggle_'+transaction_id);	
	var label = label_el.html();
	if (label == 'Show') {
		label = 'Hide';
	} else {
		label = 'Show';
	}
	label_el.html(label);
}