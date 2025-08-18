<?php

/**
 * @package Admin menü class
 */

namespace Inc\Api\Callbacks;

use \Inc\Base\BajaPublicInformationBaseController;

/**
 * 
 */
class BajaPublicInformationAdminCallbacks extends BajaPublicInformationBaseController
{
	public function adminDashboard(){

		return require_once ($this->pluginPath."templates/admin.php");
	}
 
}