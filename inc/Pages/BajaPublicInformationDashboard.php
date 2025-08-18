<?php

/**
 * @package Admin menü class
 */

namespace Inc\Pages;

use Inc\Base\BajaPublicInformationBaseController;
use Inc\Api\BajaPublicInformationSettingsApi;
use Inc\Api\Callbacks\BajaPublicInformationAdminCallbacks;
use Inc\Api\Callbacks\BajaPublicInformationManagerCallbacks;

class BajaPublicInformationDashboard extends BajaPublicInformationBaseController
{
	
	public $SettingsApiClass = array();

	public $adminMenuPages = array();

	public $adminSubMenuPages = array();

	public $callBacks = array();

	public $managerCallbacks = array();

	public $setSettings = array();

	public $setSections = array();

	public $setFields = array();


	public function registerFunction(){

		$this->callBacks = new BajaPublicInformationAdminCallbacks();

		$this->SettingsApiClass = new BajaPublicInformationSettingsApi();

		$this->managerCallbacks = new BajaPublicInformationManagerCallbacks();

		$this->setAdminPages()->addSubPages();

		//menühoz tartozó beállítások, szekciók, mezők
		$this->setSettings()
		->setSections()
		->setFields();
		//add_action('admin_menu', array($this, 'createMenu'));
		$this->SettingsApiClass->addPages($this->adminMenuPages)
		//->setSubPagesTitle('Dashboard')
		->addSettings($this->setSettings)
		->addSections($this->setSections)
		->addFields($this->setFields)
		->register();
	}


	public function setAdminPages(){

		$this->adminMenuPages = array(
			
			[
				'page_title' => "Baja lakossági információk",
				'menu_title' => "Baja lakossági információk",
				'capability' => "manage_options",
				'menu_slug' => "bpi",
				'callback' => array($this->callBacks, 'adminDashboard'),
				'icon_url' => 'dashicons-info',
				'position' => 110
			],
		);
		return $this;
	}

	public function addSubPages(){
		//sub page

		$adminPages = $this->adminMenuPages[0];


		$this->adminSubMenuPages = [

			[
				'parent_slug' => $adminPages['menu_slug'],
				'page_title' => 'Beállítások',
				'menu_title' => 'Beállítások',
				'capability' => 'manage_options',
				'menu_slug' => 'options',
				'callback' => array($this->callBacks, 'adminOptionSubmenu'),
			],

		];
		
		
	}

	public function setSettings(){
		
		//foreach ( $this->managers as $key => $value ) {
			$this->setSettings = [

				array(
					'option_group' => 'translator_desc',
					'option_name' => 'option_desc',
					'callback' => array( )
				),
				array(
					'option_group' => 'add_translator_url',
					'option_name' => 'url_scrape',
					'callback' => array( )
				),


			];
		//}

		return $this;
	}

	public function setSections(){
		
		$this->setSections = array(
			[
				'id' => 'option_section', //akarmi
				'title' => "Baja",
				'callback' => array($this->managerCallbacks, 'linkUplaoder' ),
				'page' => "option_desc"
			],
			[
				'id' => 'add_url_section', //akarmi
				'title' => "url",
				'callback' => array($this->managerCallbacks, 'addUrlInput' ),
				'page' => "url_scrape"
			],

		);

		return $this;
	}

	public function setFields(){
		
			$this->setFields = [
				
				array(
					'id' => 'option',
					'title' => 'Adatok',
					'callback' => array( $this->managerCallbacks, 'optionInputs' ),
					'page' => 'translator_scraper',
					'section' => 'translator_admin_index',
					'args' => array(
						'label_for' => 'options',
						'class' => 'ui-toggle'
					)
				),
				
			];


		return $this;
	}

}