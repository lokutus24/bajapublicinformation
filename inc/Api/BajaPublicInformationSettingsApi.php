<?php


/**
 * @package Admin menü class
 */

namespace Inc\Api;

/**
 * 
 */
class BajaPublicInformationSettingsApi
{
	
	public $adminPages = array();

	public $adminSubPages = array();

	public $settings = array();

	public $sections = array();
	
	public $fields = array();

	public function register(){

		if (!empty($this->adminPages) or !empty($this->adminSubPages)) {
			add_action('admin_menu', array($this, 'createAdminPages'));
		}

		if (!empty($this->settings)) {
			add_action('admin_init', array($this, 'registerCustomFields'));
		}
		
	}

	public function addPages($pages){

		$this->adminPages = $pages;

		return $this;
	}

	public function setSubPagesTitle(string $title = null){

		if (empty($this->adminPages)) {

		}else{

		}

		return $this; 
		

	}
	public function addSubPages(array $pages){

		$this->adminSubPages = $pages;

		return $this;
	}

	public function addSettings(array $settings){

		$this->settings = $settings;

		return $this;
	}

	public function addSections(array $sections){

		$this->sections = $sections;

		return $this;
	}

	public function addFields(array $fields){

		$this->fields = $fields;

		return $this;
	}

	public function createAdminPages(){


		/* Ez határozza meg a sidebarban lévő pluginhoz tartozó menü részeket, almenüket */
		foreach ($this->adminPages as $pages) {
			add_menu_page( $pages['page_title'], $pages['menu_title'], $pages['capability'], $pages['menu_slug'], $pages['callback'], $pages['icon_url'], $pages['position']);
		}

		foreach ($this->adminSubPages as $pages) {
			add_submenu_page( $pages['parent_slug'], $pages['page_title'], $pages['menu_title'], $pages['capability'], $pages['menu_slug'], $pages['callback']);
		}
		
	}

	public function registerCustomFields(){

		/* Ez határozza meg hogy milyen inputok legyen egy almenü vagy főmenün belül. submit button , input types, etc.. */

		//setting
		foreach ($this->settings as $setting) {
			register_setting( $setting['option_group'], $setting['option_name'], (isset($setting['callback'])) ? $setting['callback'] : '' ) ;
		}
		
		
		//sections
		foreach ($this->sections as $section) {
			add_settings_section( $section['id'], $section['title'], (isset($section['callback'])) ? $section['callback'] : '', $section['page']);
		}
		//fields.

		foreach ($this->fields as $field) {
			add_settings_field($field['id'], $field['title'], (isset($field['callback'])) ? $field['callback'] : '', $field['page'], $field['section'], (isset($field['args'])) ? $field['args'] : '');
		}
	}
}