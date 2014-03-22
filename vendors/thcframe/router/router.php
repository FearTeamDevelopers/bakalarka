<?php

namespace THCFrame\Router;

use THCFrame\Core\Base as Base;
use THCFrame\Core\Core as Core;
use THCFrame\Events\Events as Events;
use THCFrame\Router\Exception as Exception;
use THCFrame\Router\Route as Route;

/**
 * Description of Router
 *
 * @author Tomy
 */
class Router extends Base
{

    /**
     * @readwrite
     */
    protected $_url;

    /**
     * Stores the Route objects
     * @var array
     */
    protected $_routes = array();

    /**
     * @readwrite 
     * @var Route
     */
    protected $_lastRoute;
    private static $_defaultRoutes = array(
        array(
            'pattern' => '/:module/:controller/:action/:id',
            'module' => ':module',
            'controller' => ':controller',
            'action' => ':action',
            'args' => ':id',
        ),
        array(
            'pattern' => '/:module/:controller/:action/',
            'module' => ':module',
            'controller' => ':controller',
            'action' => ':action',
        ),
        array(
            'pattern' => '/:controller/:action/:id',
            'module' => 'app',
            'controller' => ':controller',
            'action' => ':action',
            'args' => ':id',
        ),
        array(
            'pattern' => '/:module/:controller/',
            'module' => ':module',
            'controller' => ':controller',
            'action' => 'index',
        ),
        array(
            'pattern' => '/:controller/:action',
            'module' => 'app',
            'controller' => ':controller',
            'action' => ':action',
        ),
        array(
            'pattern' => '/:module/',
            'module' => ':module',
            'controller' => 'index',
            'action' => 'index',
        ),
        array(
            'pattern' => '/:controller',
            'module' => 'app',
            'controller' => ':controller',
            'action' => 'index',
        ),
        array(
            'pattern' => '/',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'index',
        )
    );

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->_createRoutes(self::$_defaultRoutes);

        $modules = Core::getModules();

        foreach ($modules as $module) {
            $routes = $module->getModuleRoutes();
            $this->_createRoutes($routes);
        }

        $this->_findRoute($this->_url);
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Router\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf('%s method not implemented', $method));
    }

    /**
     * 
     */
    private function _createRoutes($routes)
    {
        foreach ($routes as $route) {
            $new_route = new Route\Dynamic(array('pattern' => $route['pattern']));

            if (preg_match('/^:/', $route['module'])) {
                $new_route->addDynamicElement(':module', ':module');
            } else {
                $new_route->setModule($route['module']);
            }

            if (preg_match('/^:/', $route['controller'])) {
                $new_route->addDynamicElement(':controller', ':controller');
            } else {
                $new_route->setController($route['controller']);
            }

            if (preg_match('/^:/', $route['action'])) {
                $new_route->addDynamicElement(':action', ':action');
            } else {
                $new_route->setAction($route['action']);
            }

            if (isset($route['args']) && preg_match('/^:/', $route['args'])) {
                $new_route->addDynamicElement($route['args'], $route['args']);
            }

            if (isset($route['args2']) && preg_match('/^:/', $route['args2'])) {
                $new_route->addDynamicElement($route['args2'], $route['args2']);
            }

            $this->addRoute($new_route);
        }
    }

    /**
     * Finds a maching route in the routes array using specified $path
     * 
     * @param string $path
     */
    private function _findRoute($path)
    {
        Events::fire('framework.router.findroute.before', array($path));

        foreach ($this->_routes as $route) {
            if (TRUE === $route->matchMap($path)) {
                $this->_lastRoute = $route;
                break;
            }
        }

        Events::fire('framework.router.findroute.after', array(
            $path,
            $this->_lastRoute->module,
            $this->_lastRoute->controller,
            $this->_lastRoute->action)
        );
    }

    /**
     * 
     * @param \THCFrame\Router\Route $route
     * @return \THCFrame\Router\Router
     */
    public function addRoute(\THCFrame\Router\Route $route)
    {
        array_unshift($this->_routes, $route);
        //$this->_routes[] = $route;
        return $this;
    }

    /**
     * 
     * @param \THCFrame\Router\Route $route
     * @return \THCFrame\Router\Router
     */
    public function removeRoute(\THCFrame\Router\Route $route)
    {
        foreach ($this->_routes as $i => $stored) {
            if ($stored == $route) {
                unset($this->_routes[$i]);
            }
        }
        return $this;
    }

    /**
     * 
     * @return array $list
     */
    public function getRoutes()
    {
        $list = array();

        foreach ($this->_routes as $route) {
            $list[$route->pattern] = get_class($route);
        }

        return $list;
    }

}
