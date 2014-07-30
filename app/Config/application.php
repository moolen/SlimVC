<?php

namespace App\Config;

return array(

	'debug' => true,
	'namespace.controller' => '\\App\\Conrollers\\',
	'slim' => array(
		// env vars
		'mode' => 'development',
		'log.enabled' => true,
		'log.writer' => new \App\Lib\SlimVC\Logger(),
		'log.level' => \Slim\Log::DEBUG,

		// view & templating
		'view' => new \Slim\Views\Twig(),
		'templates.path' => dirname(__FILE__) . '/../Views',
	)
	
);