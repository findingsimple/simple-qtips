jQuery(document).ready(function($){

	$(function() {
		$( '#simple-qtips-dialog' ).dialog({
			autoOpen: false,
			modal: true,
			dialogClass: 'wp-dialog',
			buttons: {
				Cancel: function() {
					$( this ).dialog('close');
				},
				Insert: function() {
					$(this).dialog('close');

					var qTipToInsert = '[qtip';

					$.each(qtipFields,function(id,label){
						if($('#qtip-'+id).val().length != 0){
							qTipToInsert += ' '+id+'="'+$('#qtip-'+id).val()+'"';
							$('#qtip-'+id).val('');
						}
					});

					qTipToInsert += ']';

					// HTML editor
					if (simple_qtips_caller == 'html') {
						QTags.insertContent(qTipToInsert);
					} else { // Visual Editor
						simple_qtips_canvas.execCommand('mceInsertContent', false, qTipToInsert);
					}
				}
			},
			width: 400,
		});
	});
	
	if ( typeof QTags != 'undefined' ) {
		QTags.addButton('simple_qtips_id','qtip',function(){
			simple_qtips_caller = 'html';
			jQuery('#simple-qtips-dialog').dialog('open');
		});	
	}	
});

// Global variables to keep track on the canvas instance and from what editor
// that opened the Simple qTips popup.
var simple_qtips_canvas;
var simple_qtips_caller = '';
