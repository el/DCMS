<?php

	include("app/include/Twig/Autoloader.php");
	foreach(array("src","model","controller") as $directory) {
		$directory = "app/$directory";
		if (is_dir($directory)) {
			if ($handle = opendir($directory)){
				while (($item = readdir($handle)) !== false) {
					$item = "$directory/$item";
					if (is_file($item) && substr($item, -4) == ".php")
						include($item);
				}
				closedir($handle); // Close the directory handle
			}
		} else {
			die("$directory directory could not be found");
		}
	}