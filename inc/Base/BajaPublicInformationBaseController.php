<?php

/**
 * BaseController
 */

namespace Inc\Base;


class BajaPublicInformationBaseController
{

	public $pluginPath = '';
	public $pluginName = '';
	public $pluginUrl = '';

	public function __construct(){

		$this->pluginPath = plugin_dir_path(dirname(__FILE__, 2 ));
		$this->pluginUrl = plugin_dir_url(dirname(__FILE__, 2));
		$this->pluginName = plugin_basename(dirname(__FILE__, 3))."/bpi.php";

	}

	public function activated( string $key )
	{
		$option = get_option( $key );
		return isset( $option ) ? $option : false;
	}


}