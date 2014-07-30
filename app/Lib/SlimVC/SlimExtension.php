<?php
namespace App\Lib\SlimVC;

class SlimExtension extends \Slim\Slim{

	/**
	 * PHP 5.4 Trait 
	 * inherits Methods like:
	 * 
	 * .on('event', $foo, $bar)
	 * .once('event', $foo, $bar)
	 * .off('event', $callable)
	 * .emit('event', $foo, $bar)
	 * .addListener('event', $callable)
	 * .removeListener('event', $callable)
	 */
	use \Nekoo\EventEmitter{
		\Nekoo\EventEmitter::emit as trigger;
	}

	public function emit($event, $args=null){
		$this->trigger($event, $args);
	}

}