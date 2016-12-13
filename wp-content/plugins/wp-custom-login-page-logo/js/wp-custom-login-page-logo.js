/*
*	jQuery Scripts for WP Custom Login Page Logo Plugin
*/

jQuery(document).ready(function($){

	// open thickbox w/ media upload
	$('.wpclpl-logo-upload-btn').click(function() {
		tb_show('Select an image file for custom admin logo. (Click "Insert into Post" to select it.)', 'media-upload.php?referer=wpclpl-settings&type=image&TB_iframe=true&post_id=0', false);
		return false;
	});
	
		
	// update the css...
	function updateCustomCss(){
		var backgroundImage = 'background-image:url('+$('.wpclpl-logo-url').val()+');';
		var cssPreview = backgroundImage+"\n"+$('.wpclpl-custom-css').val();
		$('#wpclpl-preview-css').html( cssPreview );
	}
		
	
	// send data to editor...
	window.send_to_editor = function(html) {
		var uploadedLogoUrl = $('img',html).attr('src');
		$('.wpclpl-logo-url').val(uploadedLogoUrl);
		tb_remove();
		$('.wpclpl-currentlogo, .wpclpl-default-logo').fadeOut(300);		
		$('<img class="wpclpl-logo-preview" style="display:none; "src="'+uploadedLogoUrl+'" />  ')
			.insertAfter('.wpclpl-currentlogo')
			.delay(500)
			.fadeIn(300);
			
		$('#wpclpl-logo-preview').attr('src', $('.wpclpl-logo-url').val() );
		$('#wpclpl-logo-preview-wrap a.thickbox').attr('href', $('.wpclpl-logo-url').val() );
		
		
		updateCustomCss();
		
		setTimeout(function(){
			$('.wpclpl-options-form').submit();
		},500);
		
		
		
	}
	
	
	function buildPreviewCss(){
	
		if( $('.wpclpl-logo-url').val() !="" ){
			var cssPreview = 'background-image:url("'+$('.wpclpl-logo-url').val()+'");';
			cssPreview += "\n";
			cssPreview += $('.wpclpl-custom-css').val();
			$('#wpclpl-preview-css').html( cssPreview );
		}
		
	}
	
	buildPreviewCss();
	
	
	// only little bit of example css...
	/*
	$('.wpclpl-logo-example-css-btn').click(function(){
		exampleCss = 'padding: 0px;'+"\n";
		exampleCss += 'background-size: cover;'+"\n";
		exampleCss += 'background-position: center center;'+"\n";
		exampleCss += 'background-repeat: no-repeat;'+"\n";
		exampleCss += 'background-color: #fff;'+"\n";
		exampleCss += 'box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.15);'+"\n";
		$('.wpclpl-custom-css').val(exampleCss);
	});
	*/
	
	// remove image and load default
	/*$('.wpclpl-logo-remove-img-btn').click(function(){
		$('.wpclpl-logo-url').val('');
		$('.wpclpl-options-form').submit();		
	});*/
	
	
	// modal window
	function wpclplShowModal(ID){
		
		$('<div class="wpclpl-modal-box-wrap"></div>').insertBefore('#wpwrap');
				console.log(ID);
		$('.'+ID).fadeIn(300,function(){
		
			// yes, reset...
			$('.wpclpl-reset-confirmed').click(function(){
				$('.wpclpl-logo-url, .wpclpl-custom-css, .wpclpl-additional-text').val('');
				$('.wpclpl-modal-box').fadeOut(300,function(){
					
					// note the jQuery bug: .submit() won't work if the submit button as a "name" tag, 
					
					if(ID=='wpclpl-modal-box-reset-image'){
						$('.wpclpl-logo-url').val('');
					}
					
					setTimeout(function(){
						$('.wpclpl-options-form').submit();	
					},1000); 
				});
			});
			
			// no, cancel
			$('.wpclpl-reset-cancel').click(function(){	
				$('.wpclpl-modal-box').fadeOut(300);
				$('.wpclpl-modal-box-wrap').fadeOut(300);
			});		
			
		});
		
	}
	
	// click on reset buttons: reset image / reset all settings
	$('.wpclpl-reset-btn').click(function(e){
		e.preventDefault();
		wpclplShowModal( $(this).attr('id') );
		
	});


	
	// update the preview while typing... just for control	
	$('.wpclpl-custom-css').keyup(function(){
		updateCustomCss();
	});
	
});