<?php

/**
 * @package Active
 */

namespace Inc\Base;

class BajaPublicInformationDeactivate
{
	
	public static function BajaPublicInformationdeactivate(){

		flush_rewrite_rules();
	}
}