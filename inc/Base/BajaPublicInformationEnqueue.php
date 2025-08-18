<?php

/**
 * Enqueue
 */

namespace Inc\Base;

use Inc\Base\BajaPublicInformationBaseController;
use Inc\Base\BajaPublicInformation;

class BajaPublicInformationEnqueue extends BajaPublicInformationBaseController
{
	

	public function registerFunction(){

		add_action('admin_enqueue_scripts', array($this, 'customFiles'));

		add_action("wp_ajax_setBajaPublicInformation", array($this, "setBajaPublicInformation" ) );

		add_action("wp_ajax_nopriv_setBajaPublicInformation", array($this, "setBajaPublicInformation") );

	}

	public function customFiles(){

		/*
		* load custom styles and scripts
		*/
		wp_enqueue_style('translator', $this->pluginUrl.'assets/bpi.css');
		//wp_enqueue_script('translator', $this->pluginUrl.'assets/translator.js', array( 'jquery' ));

		/**
		 * Ajax handle
		 */
	  	wp_enqueue_script( 'ajaxHandle', plugin_dir_url(dirname(__FILE__, 3)).'bajapublicinformation/assets/bpi.js', array( 'jquery' ));
	  	wp_localize_script( 'ajaxHandle', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	}

	public function setBajaPublicInformation(){

		$obj = new BajaPublicInformation();
  		$result = $obj->getPostContent($_POST['post_id'], $_POST['target_lang']);

  		if ( !isset($result['error']) or !empty($result) ) {
  			$status = 'success';
  		}else{
  			$status = 'error';
  		}

		$returnData = array('status' => $status, 'errormsg' => $result);

		echo json_encode($returnData);

		wp_die(); // ajax call must die to avoid trailing 0 in your response
	}

}