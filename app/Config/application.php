<?php

namespace App\Config;

return array(
	// these are basically the defaults; except debug mode & logging enabled
	'debug' => true,
	'namespace.controller' => '\\App\\Conrollers\\',
	'method.seperator' => '::',
	'log.enabled' => true,
	'log.level' => 8,
	'templates.path' => dirname(__FILE__) . '/../views'
);