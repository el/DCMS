<?php

	$controller;
	Twig_Autoloader::register();

	$twig_loader = new Twig_Loader_Filesystem('app/view');
	$twig = new Twig_Environment($twig_loader, array(
    	'cache' => 'app/cache',
    	'debug' => true,
	));

	Router::serve(array(
		"" => "home::index",
		));