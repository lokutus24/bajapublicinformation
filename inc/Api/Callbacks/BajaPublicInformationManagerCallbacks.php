<?php 
/**
 * @package  AlecadddPlugin
 */
namespace Inc\Api\Callbacks;

use Inc\Base\BajaPublicInformationBaseController;
use Inc\Base\BajaPublicInformation;


class BajaPublicInformationManagerCallbacks extends BajaPublicInformationBaseController
{

	public function adminSectionManager(){

		echo 'A beállított adatok segítségével fordítja át a bejegyzéseket!';
	}

	public function linkUplaoder(){

		echo 'Lakossági információk';

	}

}