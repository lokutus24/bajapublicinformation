<?php

/**
 * @package Active
 */

namespace Inc\Base;

class BajaPublicInformationActivate
{	

	public static function BajaPublicInformationactivate(){

		flush_rewrite_rules();

		$default = array();

		if ( ! get_option( 'bpi' ) ) {
			update_option( 'bpi', $default );
		}
	}

}