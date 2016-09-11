<?php

	/**
	 * Validation class
	 */
	class Val {

		/**
		 * Removes all quotes
		 * @param  string $data
		 * @return string
		 */
		public static function title( $data ) {
			$data = str_replace("'", "", $data);
			$data = str_replace("`", "", $data);
			return   str_replace('"', "", $data);
		}
		
		/**
		 * Alias for Val::title
		 * @param  string $data
		 * @return string
		 */
		public static function name( $data ) {
			return self::title($data);
		}
		
		/**
		 * Check if string in md5 format
		 * @param  string $data
		 * @return string
		 */
		public static function pass( $data ) {
  			if(preg_match('/^[a-f0-9_]{32}$/', $data))
    			return $data;
  			else
  				return md5($data);
		}
		
		/**
		 * Check if input is a number
		 * @param  string $data
		 * @return float
		 */
		public static function num( $data ){
  			$num = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
			if ($num=="") $num=0;
			return $num;
		}

		/**
		 * Create a safe string from the input
		 * @param  string $fname
		 * @return string
		 */
		public static function safe( $fname ) {
			$replace="_";
			$fname = str_replace(array("ç","Ç","ğ","Ğ","ü","Ü","ö","Ö","ş","Ş","ı","İ"),array("c","C","g","G","u","U","o","O","s","S","i","I"),$fname);
			$pattern="/([a-zA-Z0-9\.\/\\-]*)/";
			$fname=str_replace(str_split(preg_replace($pattern,$replace,$fname)),$replace,$fname);
			return $fname;
		}

		/**
		 * Safe and lowercase string
		 * @param  string $name
		 * @return string
		 */
		public static function safes( $name ) {
			return strtolower(self::safe($name));
		}

	}
