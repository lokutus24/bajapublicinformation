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

                // front-end assets
                add_action('wp_enqueue_scripts', array($this, 'publicFiles'));

                add_action("wp_ajax_setBajaPublicInformation", array($this, "setBajaPublicInformation" ) );

                add_action("wp_ajax_nopriv_setBajaPublicInformation", array($this, "setBajaPublicInformation") );

        }

        public function customFiles(){

                /*
                * load custom styles and scripts for admin
                */
                wp_enqueue_style('translator', $this->pluginUrl.'assets/bpi.css');

                /**
                 * Ajax handle
                 */
                wp_enqueue_script( 'ajaxHandle', plugin_dir_url(dirname(__FILE__, 3)).'bajapublicinformation/assets/bpi.js', array( 'jquery' ));
                wp_localize_script( 'ajaxHandle', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

        }

        /**
         * Enqueue public facing assets
         */
        public function publicFiles(){

                wp_enqueue_style('bpi-front', $this->pluginUrl.'assets/frontend.css', array(), null);
                wp_enqueue_script('bpi-front', $this->pluginUrl.'assets/frontend.js', array('jquery'), null, true);
                wp_localize_script('bpi-front', 'bpiAjax', array('ajax_url' => admin_url('admin-ajax.php')));

                // Ensure proper scaling on mobile devices
                add_action('wp_head', array($this, 'addViewportMeta'));

        }

        /**
         * Output viewport meta tag for responsive layouts
         */
        public function addViewportMeta(){
                echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
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