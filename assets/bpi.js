window.addEventListener("load", function() {

	// store tabs variables
	var tabs = document.querySelectorAll("ul.nav-tabs > li");

	for (i = 0; i < tabs.length; i++) {
		tabs[i].addEventListener("click", switchTab);
	}

	function switchTab(event) {
		event.preventDefault();

		document.querySelector("ul.nav-tabs li.active").classList.remove("active");
		document.querySelector(".tab-pane.active").classList.remove("active");

		var clickedTab = event.currentTarget;
		var anchor = event.target;
		var activePaneID = anchor.getAttribute("href");

		clickedTab.classList.add("active");
		document.querySelector(activePaneID).classList.add("active");

	}

	

	var idsArr = [];
	jQuery('#blog_post_title_save').on('click', function(){

		jQuery('#blog_post_checkbox:checked').each(function(data){
			//console.log( jQuery(this).val() );
			idsArr.push(jQuery(this).val());
		})

		jQuery.ajax({
	      url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
	      type: 'POST',
	      data:{ 
	        action:'blog_post_meta', // this is the function in your functions.php that will be triggered
	        ids: idsArr,
	        postid: jQuery('#blog_post_title_save').data('postid')
	      },
	      success: function( data ){
	        //Do something with the result from server

	      },
	      error: function(error){
	        //console.log( error );
	      }

	    });
		console.log(idsArr);
	})

	jQuery("#blog_post_title").keyup(function( event ) {
        //kereső
        event.preventDefault();

        console.log( jQuery(this).val() );

        jQuery.ajax({
	      url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
	      type: 'POST',
	      data:{ 
	        action:'data_fetch', // this is the function in your functions.php that will be triggered
	        blog_post: jQuery(this).val(),
	        ids: jQuery('#blog_post_title').data('ids')
	      },
	      success: function( data ){
	        //Do something with the result from server
	        jQuery('#datafetch').html( data );

	      },
	      error: function(error){
	        //console.log( error );
	      }

	    });
        
     })

});


jQuery( document ).ready(function($) {

	jQuery('.cf_tranlator_btn').click(function(){
		
		$('#loading_cf_translator').show();
		$.ajax({
	      url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
	      type: 'POST',
	      dataType: "JSON",
	      data:{ 
	        action:'setTranslator', // this is the function in your functions.php that will be triggered
	        post_id: $('.cf_tranlator_btn').attr('postid'),
	        target_lang: $('#cf_target_lang').val(),
	      },
	      //cache: false,
	      success: function( response ){
	        //Do something with the result from server
	       	
	       	console.log(response);
	       	if (response.status == 'success') {
	       		document.location.reload(true);
	       	}else{
	       		jQuery('#erromsgcftranslator').text(response.error+" "+response.errormsg).show();
	       	}

	       	$('#loading_cf_translator').hide();
	        
	      },
	      error: function(error){
	        alert('hiba');
	      }

	    });

	    
	
	})

})
