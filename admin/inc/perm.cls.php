<?php 

	/**
	 * Permissions are stored in this object and can be manipulated.
	 */
	Class Perm {
		
		const Read 		=  1;
		const Write 	=  2;
		const Edit 		=  4;
		const Approve 	=  8;
		const Mod		= 16;
		const Show		= 32;
		const Remove	= 64;
		const Fill 		= 128;
		
		public $permissions = 0;
		public $first_permissions = 0;
		public $section 	= "";
		
		function __construct($permissions = 0, $section = "") {
			$this->permissions = $permissions;
			$this->first_permissions = $permissions;
			$this->section = $section;
		}
		
		/**
		 * Get names
		 * @param  string $s
		 * @return string
		 */
		static public function name($s) {
			$arr = array(
				"Read" 		=> "Okuma",
				"Write"		=> "Ekleme",
				"Approve"	=> "Onaylama",
				"Edit"		=> "Düzenleme",
				"Mod"		=> "İzin Ayarı",
				"Show"		=> "Bölüm",
				"Remove"	=> "Silme",
				"Fill"		=> "Doldurma",
			);
			if (isset($arr[$s]))
				return $arr[$s];
			else 
				return "";
		}
		
		/**
		 * Check if this permission is present
		 * @param  string  $perm
		 * @return boolean
		 */
		public function is($perm) {
			return $this->permissions & constant("Perm::$perm"); 
		}
		
		/**
		 * Reset permission
		 * @param integer $p
		 */
		public function set($p) {
			return $this->permissions = $p;
		}
		
		/**
		 * Get the permission
		 * @return integer
		 */
		public function get() {
			return $this->permissions; 
		}
		
		/**
		 * Check if permission is changed later
		 * @return bool
		 */
		public function changed() {
			return $this->permissions != $this->first_permissions; 
		}
		
		/**
		 * Add a permission with integer
		 * @param  integer $perm
		 * @return integer
		 */
		public function in($perm) {
			$this->permissions = $this->permissions | $perm; 
			return $this->permissions;
		}
		
		/**
		 * Add a permission with its name
		 * @param string $perm
		 */
		public function add($perm) {
			$this->permissions = $this->permissions | constant("Perm::$perm"); 
			return $this->permissions;
		}
		
		/**
		 * Remove a permission with its name
		 * @param  string $perm
		 * @return integer
		 */
		public function remove($perm) {
			if ($this->permissions & constant("Perm::$perm"))
				$this->permissions = $this->permissions - constant("Perm::$perm"); 
			return $this->permissions;
		}
		
	}