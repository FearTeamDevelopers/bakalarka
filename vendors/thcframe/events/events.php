<?php

namespace THCFrame\Events;

use THCFrame\Registry\Registry;

/**
 * Observer
 * 
 * @author Tomy
 */
class Events
{

    private static $_callbacks = array();
    private static $_instatnce = null;

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    /**
     * 
     * @return type
     */
    public static function getInstance()
    {
        if (NULL === self::$_instatnce) {
            self::$_instatnce = new self();
            return self::$_instatnce;
        } else {
            return self::$_instatnce;
        }
    }

    /**
     * 
     */
    public function initialize()
    {
        Events::fire('framework.events.initialize.before', $this);

        $configuration = Registry::get('config');

        if (!empty($configuration->observer->events)) {
            $events = (array) $configuration->observer->events;

            foreach ($events as $event => $callback) {
                self::add($event, $callback);
            }
        }

        Events::fire('framework.events.initialize.after', $this);
    }

    /**
     * 
     * @param type $type
     * @param type $callback
     */
    public static function add($type, $callback)
    {
        if (empty(self::$_callbacks[$type])) {
            self::$_callbacks[$type] = array();
        }

        self::$_callbacks[$type][] = $callback;
    }

    /**
     * 
     * @param type $type
     * @param type $parameters
     */
    public static function fire($type, $parameters = null)
    {
        if (!empty(self::$_callbacks[$type])) {
            foreach (self::$_callbacks[$type] as $callback) {
                if (is_callable($callback)) {
                    call_user_func_array($callback, $parameters);
                } else {
                    $parts = explode(".", $type);
                    $moduleObject = \THCFrame\Core\Core::getModule($parts[0]);
                    $observerClass = $moduleObject->getObserverClass();
                    $observer = new $observerClass;
                    $observer->$callback($parameters);
                }
            }
        }
    }

    /**
     * 
     * @param type $type
     * @param type $callback
     */
    public static function remove($type, $callback)
    {
        if (!empty(self::$_callbacks[$type])) {
            foreach (self::$_callbacks[$type] as $i => $found) {
                if ($callback == $found) {
                    unset(self::$_callbacks[$type][$i]);
                }
            }
        }
    }

}
