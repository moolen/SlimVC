<?php

namespace App\Lib\SlimVC;

/**
 * Event Emitter
 *
 * This is a slightly modified version that works with PHP 5.3.
 * Traits are nice, but 5.3 still has to be supported. Like IE8. :(
 *
 * @copyright Copyright (C) 2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license https://raw.github.com/fruux/sabre-event/master/LICENSE
 */
class EventEmitter {

    /**
     * The list of listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Subscribe to an event.
     *
     * @param string $eventName
     * @param callable $callBack
     * @param int $priority
     * @return void
     */
    public function on($eventName, $callBack, $priority = 100) {

        $listeners =& $this->listeners($eventName);
        $listeners[] = array($priority, $callBack);
        usort($listeners, function($a, $b) {
            return $a[0]-$b[0];
        });

    }

    /**
     * Subscribe to an event exactly once.
     *
     * @param string $eventName
     * @param callable $callBack
     * @param int $priority
     * @return void
     */
    public function once($eventName, $callBack, $priority = 100) {

        $wrapper = null;
        $wrapper = function() use ($eventName, $callBack, &$wrapper) {

            $this->removeListener($eventName, $wrapper);
            $result = call_user_func_array($callBack, func_get_args());

        };

        $this->on($eventName, $wrapper);

    }

    /**
     * Emits an event.
     *
     * This method will return true if 0 or more listeners were succesfully
     * handled. false is returned if one of the events broke the event chain.
     *
     * @param string $eventName
     * @param array $arguments
     * @return bool
     */
    public function emit($eventName, array $arguments = array()) {

        foreach($this->listeners($eventName) as $listener) {

            $result = call_user_func_array($listener[1], $arguments);
            if ($result === false) {
                return false;
            }
        }

        return true;

    }


    /**
     * Returns the list of listeners for an event.
     *
     * The list is returned as an array. Every item is another array with 2
     * elements: priority and the callback.
     *
     * The array is returned by reference, and can therefore be used to
     * manipulate the list of events.
     *
     * @param string $eventName
     * @return array
     */
    public function & listeners($eventName) {

        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = array();
        }

        return $this->listeners[$eventName];

    }

    /**
     * Removes a specific listener from an event.
     *
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function removeListener($eventName, $listener) {

        $listeners =& $this->listeners($eventName);
        foreach($listeners as $index => $check) {
            if ($check[1]===$listener) {
                unset($listeners[$index]);
                break;
            }
        }

    }

    /**
     * Removes all listeners from the specified event.
     *
     * @param string $eventName
     * @return void
     */
    public function removeAllListeners($eventName) {

        $listeners =& $this->listeners($eventName);
        $listeners = array();

    }

}