<?php

namespace THCFrame\Router;

use THCFrame\Core\Base as Base;
use THCFrame\Events\Events as Events;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Core\Inspector as Inspector;
use THCFrame\Router\Exception as Exception;

/**
 * Description of Dispatcher
 *
 * @author Tomy
 */
class Dispatcher extends Base
{

    /**
     * The suffix used to append to the class name
     * @var string
     * @read
     */
    protected $_suffix;

    /**
     * The path to look for classes (or controllers)
     * @var string
     * @read
     */
    protected $_controllerPath;

    /**
     * 
     */
    public function initialize()
    {
        Events::fire("framework.dispatcher.initialize.before", array());

        $configuration = Registry::get("configParsed");

        if (empty($configuration)) {
            $configuration = $configuration->initialize();

            if (DEBUG) {
                $parsed = $configuration->parse("configuration/config_dev");
            } else {
                $parsed = $configuration->parse("configuration/config");
            }

            if (!empty($parsed->dispatcher->default)) {
                $this->_setSuffix(".php");
            }
        } else {
            if (!empty($configuration->dispatcher->default)) {
                $this->_setSuffix(".php");
            }
        }

        Events::fire("framework.dispatcher.initialize.after", array());
        
        return $this;
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Router\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * Sets a suffix to append to the class name being dispatched
     * 
     * @param string $suffix
     * @return \THCFrame\Router\Dispatcher
     */
    protected function _setSuffix($suffix)
    {
        $this->_suffix = $suffix;

        return $this;
    }
    
    /**
     * 
     * @return type
     */
    protected function _getSuffix(){
        return $this->_suffix;
    }

    /**
     * Set the path where dispatch class (controllers) reside
     * 
     * @param string $path
     * @return \THCFrame\Router\Dispatcher
     */
    protected function _setControllerPath($path)
    {
        $this->_controllerPath = preg_replace("/\/$/", "", $path) . "/";

        return $this;
    }

    /**
     * Attempts to dispatch the supplied Route object. 
     * 
     * @param \THCFrame\Router\Route $route
     * @throws Exception\Module
     * @throws Exception\Controller
     * @throws Exception\Action
     */
    public function dispatch(\THCFrame\Router\Route $route)
    {
        
        $module = trim($route->module);
        $class = trim($route->controller);
        $action = trim($route->action);
        $parameters = $route->getMapArguments();

        if ("" === $module) {
            throw new Exception\Module("Module Name not specified");
        } elseif ("" === $class) {
            throw new Exception\Controller("Class Name not specified");
        } elseif ("" === $action) {
            throw new Exception\Action("Method Name not specified");
        }

        $module = str_replace("\\", "", $module);
        preg_match("/^[a-zA-Z0-9_]+$/", $module, $matches);
        if (count($matches) !== 1) {
            throw new Exception\Module(sprintf("Disallowed characters in module name %s", $module));
        }

        $class = str_replace("\\", "", $class);
        preg_match("/^[a-zA-Z0-9_]+$/", $class, $matches);
        if (count($matches) !== 1) {
            throw new Exception\Controller(sprintf("Disallowed characters in class name %s", $class));
        }

        $file_name = strtolower("./modules/{$module}/controller/{$class}.php");
        $class = ucfirst($module) . "_Controller_" . ucfirst($class);

        if (FALSE === file_exists($file_name)) {
            throw new Exception\Controller(sprintf("Class file %s not found", $file_name));
        } else {
            require_once($file_name);
        }

        Events::fire("framework.dispatcher.controller.before", array($class, $parameters));

        try {
            $instance = new $class(array(
                "parameters" => $parameters
            ));
            Registry::set("controller", $instance);
        } catch (\Exception $e) {
            throw new Exception\Controller(sprintf("Controller %s error: %s", $class, $e->getMessage()));
        }

        Events::fire("framework.dispatcher.controller.after", array($class, $parameters));

        if (!method_exists($instance, $action)) {
            $instance->willRenderLayoutView = false;
            $instance->willRenderActionView = false;

            throw new Exception\Action(sprintf("Action %s not found", $action));
        }

        $inspector = new Inspector($instance);
        $methodMeta = $inspector->getMethodMeta($action);

        if (!empty($methodMeta["@protected"]) || !empty($methodMeta["@private"])) {
            throw new Exception\Action(sprintf("Action %s not found", $action));
        }

        $hooks = function($meta, $type) use ($inspector, $instance) {
            if (isset($meta[$type])) {
                $run = array();

                foreach ($meta[$type] as $method) {
                    $hookMeta = $inspector->getMethodMeta($method);

                    if (in_array($method, $run) && !empty($hookMeta["@once"])) {
                        continue;
                    }

                    $instance->$method();
                    $run[] = $method;
                }
            }
        };

        Events::fire("framework.dispatcher.beforehooks.before", array($action, $parameters));

        $hooks($methodMeta, "@before");

        Events::fire("framework.dispatcher.beforehooks.after", array($action, $parameters));
        Events::fire("framework.dispatcher.action.before", array($action, $parameters));

        call_user_func_array(array(
            $instance, $action), is_array($parameters) ? $parameters : array());

        Events::fire("framework.dispatcher.action.after", array($action, $parameters));
        Events::fire("framework.dispatcher.afterhooks.before", array($action, $parameters));

        $hooks($methodMeta, "@after");

        Events::fire("framework.dispatcher.afterhooks.after", array($action, $parameters));

        // unset controller

        Registry::erase("controller");
    }

}