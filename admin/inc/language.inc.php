<?php
	/**
	 * Multi-language related activities
	 */
	date_default_timezone_set($site["timezone"]);
	session_start();
	define('ROOT', str_replace(array("\inc","/inc"),array("",""),dirname(realpath(__FILE__)).'/'));

	$LAN = isset($_SESSION["language"]) ? $_SESSION["language"] : $site["default_language"];
	$keys = array_keys($site["languages"]);
	if (sizeof($site["languages"]))
		require_once __DIR__.'/locale/'.$keys[$LAN].".inc.php";

	function _t($term='')
	{
		global $site;
		if (sizeof($site["languages"])==1 || @!$_SESSION["language"])
			return $term;
		global $LANG;
		foreach ($LANG as $terms) {
			if ($terms["term"]==$term) return $terms["definition"];
		}
		return $term;
	}

	function t($v="",$a=false) {
		$v = _t($v);
		if (!is_array($a) && $a!==false) {
			$v = str_replace('$$', $a, $v);
		}elseif (is_array($a)) {
			for ($i=1; $i <= sizeof($a); $i++) { 
				$v = str_replace('$'.$i, $a[$i], $v);
			}
		}
		return $v;
	}