<?php

namespace App\Config;

return array(

	'debug' => true,
	'namespace.controller' => '\\App\\Conrollers\\',
	'method.seperator' => ':',
	'log.enabled' => true,
	'log.level' => 8,
	'templates.path' => dirname(__FILE__) . '/../views'
);