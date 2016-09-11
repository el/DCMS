<?php

	class extVeritronik extends Extensions {

		public $info = array(
			"name"		=>	"Veritronik",
			"version"	=>	"1.0.0",
			"menu"		=>	false,
			"assets"	=>	array(
				"js"	=>	array(
					"ext/veritronik/script.js"
				),
				"css"	=>	array(
					"ext/veritronik/style.css"
				),
			),
		);
		
		function __construct() {

		}
		
		public function load() {
			global $site,$_SESSION, $_GET;
			if ($_SESSION["type"]!=0) return "<style>.in-page-0imodul, .in-page-0iid {display:none;}</style>";
			return "";
		}

		public function redirect() {
		}	

		public function manage() {
			global $site,$_SESSION,$dbh,$_GET,$_SERVER;
			$out = "";
			return $out;
		}

		public function api($api) {
			switch($api->action) {
				case "deneme":
					$this->deneme($api);
					break;
			}
		}
		private function deneme($api) {
			die("deneme :)");
		}

	}
