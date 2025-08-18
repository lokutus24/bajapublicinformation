<?php
/**
* Plugin Name: Baja Lakossági Információk
* Plugin URI: https://lionstack.hu
* Description: Visszaadja a bajai lakossági információkat
* Version: 1.0
* Author: Lion Stack Kft.
* Author URI: https://lionstack.hu
**/

defined('ABSPATH') or die('nem kéne..');



if (file_exists(dirname(__FILE__)."/vendor/autoload.php")) {
  require_once dirname(__FILE__)."/vendor/autoload.php";
}


function BajaPublicInformationactivate(){
	Inc\Base\BajaPublicInformationActivate::BajaPublicInformationactivate();
}

function BajaPublicInformationdeactivate(){
	Inc\Base\BajaPublicInformationDeactivate::BajaPublicInformationdeactivate();
}

register_activation_hook(__FILE__, 'BajaPublicInformationactivate');

register_deactivation_hook(__FILE__, 'BajaPublicInformationdeactivate');

if (class_exists('Inc\\Initial')) {
	Inc\Initial::BajaPublicInformationserviceRegister();

}