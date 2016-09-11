<?php
	/**
	 * Extensions file
	 */

	/**
	 * Abstract class of the extensions
	 * 
	 * In order to extend the functionality
	 * of the system you need to use extensions. You should not change any code else where!
	 *
	 * Extensions always should be in their directory {sample} and have the name ext{Sample}
	 * with uppercase start. 
	 *
	 * File Structure: 
	 * ext/veritronik/veritronik.ext.php
	 *
	 * You may include any number of file you need in that directory, however
	 * only the extension file will be loaded automatically.
	 *
	 * @usage class extDeneme extends Extensions {}
	 */
	class Extensions {
		/**
		 * $info array contains the information about the extension
		 *
		 * [name] is the name of the extension as seen by the manage page header. 
		 * 
		 * [version] in order to support automatic updates of the extension you can use the version.
		 * 
		 * [menu] If the extension have a interface, then it should be added to the side menu
		 * 
		 * [settings] Extension setting can be hold outside of the extension. This variable contains the
		 * initial settings.
		 *
		 * [assets] If your extension needs to include javascript or css file this is the place to link them.
		 * 
		 * @var array
		 */
		public $info = array(
			"name"		=>	"",
			"version"	=>	"1.0.0",
			"menu"		=>	false,
			"settings"	=>	false,
			"assets"	=>	array(
				"js"	=>	array(),
				"css"	=>	array(),
			),
		);
		/**
		 * Runs when the initialization of the extension which is before any output.
		 */
		function __construct() {}

		/**
		 * Runs after menu output and should contain the code to run. 
		 * 
		 * Returns the string that should be included in the section.
		 * 
		 * @return [string]
		 */
		public function load() {return "";}

		/**
		 * Settings changing 
		 * @return [bool]
		 */
		public function settings() {return false;}

		/**
		 * Hook is not yet implemented
		 * @return [bool]
		 */
		public function hook() {return false;}

		/**
		 * Web service api calls are made by this method. Best use is to check for $api->action
		 * and act accordingly. $api is the Api class instance that you may want to use.
		 * @param  [Api] $api
		 */
		public function api($api) {return;}

		/**
		 * Lots of things in the web interface are made by ajax calls to the /system/ajax.php
		 *
		 * This method is run before any action. Best practice is to check $_POST array and act accordingly.
		 *
		 * @usage switch($api->action) {
		 * 	case "deneme":
		 * 		$this->deneme($api);
		 * 		break;
		 * 	}
		 */
		public function ajax() {return;}

		/**
		 * Redirect is not yet implemented.
		 */
		public function redirect() {return;}

		/**
		 * If you set your $info["menu"] = true then this method is called when user wants to access to extension.
		 * @return [string]
		 */
		public function manage() {return "";}

	}
