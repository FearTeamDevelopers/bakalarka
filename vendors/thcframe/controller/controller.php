<?php

namespace THCFrame\Controller;

use THCFrame\Core\Base as Base;
use THCFrame\View\View as View;
use THCFrame\Events\Events as Events;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Controller\Exception as Exception;
use THCFrame\View\Exception as ViewException;

/**
 * Description of Controller
 *
 * @author Tomy
 */
class Controller extends Base
{

    /**
     * @read
     */
    protected $_name;

    /**
     * @readwrite
     */
    protected $_parameters;

    /**
     * @readwrite
     */
    protected $_layoutView;

    /**
     * @readwrite
     */
    protected $_actionView;

    /**
     * @readwrite
     */
    protected $_willRenderLayoutView = true;

    /**
     * @readwrite
     */
    protected $_willRenderActionView = true;

    /**
     * @readwrite
     */
    protected $_defaultPath = "modules/%s/views";

    /**
     * @readwrite
     */
    protected $_defaultLayout = "layouts/basic";

    /**
     * @readwrite
     */
    protected $_defaultExtension = "phtml";

    /**
     * @readwrite
     */
    protected $_defaultContentType = "text/html";

    /**
     * 
     * @return type
     */
    protected function getName()
    {
        if (empty($this->_name)) {
            $this->_name = get_class($this);
        }
        return $this->_name;
    }

    /**
     * 
     * @param type $method
     * @return \THCFrame\Controller\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * 
     * @param type $url
     */
    public static function redirect($url = null)
    {
        if (NULL === $url) {
            header("Location: /");
            exit();
        } else {
            header("Location: {$url}");
            exit();
        }
    }

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        Events::fire("framework.controller.construct.before", array($this->name));

        $configuration = Registry::get("configParsed");

        if (empty($configuration)) {
            $configuration = $configuration->initialize();

            if (DEBUG) {
                $parsed = $configuration->parse("configuration/config_dev");
            } else {
                $parsed = $configuration->parse("configuration/config");
            }

            if (!empty($parsed->view->default)) {
                $this->defaultExtension = $parsed->view->default->extension;
                $this->defaultLayout = $parsed->view->default->layout;
                $this->defaultPath = $parsed->view->default->path;
            }
        } else {
            if (!empty($configuration->view->default)) {
                $this->defaultExtension = $configuration->view->default->extension;
                $this->defaultLayout = $configuration->view->default->layout;
                $this->defaultPath = $configuration->view->default->path;
            }
        }

        $router = Registry::get("router");
        $module = $router->getLastRoute()->getModule();
        $controller = $router->getLastRoute()->getController();
        $action = $router->getLastRoute()->getAction();

        $defaultPath = sprintf($this->defaultPath, $module);
        $defaultLayout = $this->defaultLayout;
        $defaultExtension = $this->defaultExtension;

        if ($this->willRenderLayoutView) {
            $view = new View(array(
                "file" => APP_PATH . "/{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
            ));

            $this->layoutView = $view;
        }

        if ($this->willRenderActionView) {
            $view = new View(array(
                "file" => APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
            ));

            $this->actionView = $view;
        }

        Events::fire("framework.controller.construct.after", array($this->name));
    }

    /**
     * 
     * @param type $model
     */
    public function getModel($model, $options = NULL)
    {
        list($module, $modelName) = explode("/", $model);

        if ($module == "" || $modelName == "") {
            throw new Exception\Model(sprintf("%s is not valid model name", $model));
        } else {
            $fileName = APP_PATH . strtolower("/modules/{$module}/model/{$modelName}.php");
            $className = ucfirst($module) . "_Model_" . ucfirst($modelName);

            if (file_exists($fileName)) {
                if (NULL !== $options) {
                    return new $className($options);
                } else {
                    return new $className();
                }
            }
        }
    }

    /**
     * 
     * @throws View\Exception\Renderer
     */
    public function render()
    {
        Events::fire("framework.controller.render.before", array($this->name));

        $defaultContentType = $this->defaultContentType;
        $results = null;

        $doAction = $this->willRenderActionView && $this->actionView;
        $doLayout = $this->willRenderLayoutView && $this->layoutView;

        try {
            if ($doAction) {
                $view = $this->actionView;
                $results = $view->render();

                $this->actionView
                        ->template
                        ->implementation
                        ->set("action", $results);
            }

            if ($doLayout) {
                $view = $this->layoutView;
                $results = $view->render();

                header("Content-type: {$defaultContentType}");
                echo $results;
            } else if ($doAction) {
                header("Content-type: {$defaultContentType}");
                echo $results;
            }

            $this->willRenderLayoutView = false;
            $this->willRenderActionView = false;
        } catch (\Exception $e) {
            throw new ViewException\Renderer("Invalid layout/template syntax");
        }

        Events::fire("framework.controller.render.after", array($this->name));
    }

    /**
     * 
     */
    public function __destruct()
    {
        Events::fire("framework.controller.destruct.before", array($this->name));

        $this->render();

        Events::fire("framework.controller.destruct.after", array($this->name));
    }

}
