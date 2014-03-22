<?php

namespace THCFrame\Core;

use THCFrame\Core\Exception as Exception;
use THCFrame\Registry\Registry as Registry;

class Core
{

    private static $_errorLog;
    private static $_pathToLogs;
    private static $_systemLog;
    private static $_modules = array();
    private static $_relPaths = array(
        './vendors',
        './application',
        './modules',
        '.'
    );

    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    /**
     * 
     * @param type $array
     * @return type
     */
    private static function _clean($array)
    {
        if (is_array($array)) {
            return array_map(__CLASS__ . '::_clean', $array);
        }
        return stripslashes(trim($array));
    }

    /**
     * 
     */
    private static function _logCleanUp()
    {
        $logsPath = self::$_pathToLogs;
        $iterator = new \DirectoryIterator($logsPath);
        $arr = array();

        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                $arr[] = $logsPath . '/' . $item->getFilename();
            }
        }

        $arrev = array_reverse($arr);
        $count = count($arrev);

        for ($i = 15; $i < $count; $i++) {
            unlink($arrev[$i]);
        }
    }

    /**
     * 
     * @param type $class
     * @return type
     * @throws Exception
     */
    protected static function _autoload($class)
    {
        //$paths = explode(PATH_SEPARATOR, get_include_path());
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
        $file = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, trim($class, '\\'))) . '.php';

        foreach (self::$_relPaths as $path) {
            $combined = $path . DIRECTORY_SEPARATOR . $file;

            if (file_exists($combined)) {
                require_once($combined);
                return;
            }
        }

        $file = strtolower(str_replace('_', DIRECTORY_SEPARATOR, trim($class, '\\'))) . '.php';
        foreach (self::$_relPaths as $path) {
            $combined = $path . DIRECTORY_SEPARATOR . $file;

            if (file_exists($combined)) {
                require_once($combined);
                return;
            }
        }

        throw new \Exception(sprintf('%s not found', $class));
    }

    /**
     * 
     * @param type $message
     * @param type $file
     */
    public static function log($message, $file = null)
    {
        $messageE = '[' . date('Y-m-d H:i:s', time()) . '] DEBUG: ' . $message . PHP_EOL;

        if (NULL !== $file) {
            file_put_contents(APP_PATH . '/application/logs/' . $file, $messageE, FILE_APPEND);
        } else {
            file_put_contents(APP_PATH . '/application/logs/system.log', $messageE, FILE_APPEND);
        }
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public static function initialize()
    {
        if (!defined('APP_PATH')) {
            throw new Exception('APP_PATH not defined');
        }

        // fix extra backslashes in $_POST/$_GET
        if (get_magic_quotes_gpc()) {
            $globals = array('_POST', '_GET', '_COOKIE', '_REQUEST', '_SESSION');

            foreach ($globals as $global) {
                if (isset($GLOBALS[$global])) {
                    $GLOBALS[$global] = self::_clean($GLOBALS[$global]);
                }
            }
        }

        // start autoloading
        spl_autoload_register(__CLASS__ . '::_autoload');

        //logs paths
        self::$_pathToLogs = APP_PATH . '/application/logs';
        self::$_systemLog = APP_PATH . '/application/logs/system.txt';
        self::$_errorLog = APP_PATH . '/application/logs/' . date('Y-m-d') . '-errorLog.txt';

        // remove old log files
        self::_logCleanUp();

        // error and exception handlers
        set_error_handler(__CLASS__ . '::_errorHandler');
        set_exception_handler(__CLASS__ . '::_exceptionHandler');
    }

    /**
     * 
     * @param type $moduleArray
     */
    public static function registerModules($moduleArray)
    {
        foreach ($moduleArray as $moduleName) {
            self::registerModule($moduleName);
        }
    }

    /**
     * 
     * @throws \THCFrame\Module\Exception\Multiload
     */
    public static function registerModule($moduleName)
    {
        if (array_key_exists(ucfirst($moduleName), self::$_modules)) {
            throw new \THCFrame\Module\Exception\Multiload(sprintf('Module %s has been alerady loaded', ucfirst($moduleName)));
        } else {
            $moduleClass = ucfirst($moduleName) . '_Etc_Module';

            try {
                $moduleObject = new $moduleClass();
                $moduleObjectName = ucfirst($moduleObject->getModuleName());
                self::$_modules[$moduleObjectName] = $moduleObject;
            } catch (Exception $e) {
                
            }
        }
    }

    /**
     * 
     * @param type $moduleName
     * @return null
     */
    public static function getModule($moduleName)
    {
        $moduleName = ucfirst($moduleName);

        if (array_key_exists($moduleName, self::$_modules)) {
            return self::$_modules[$moduleName];
        } else {
            return null;
        }
    }

    /**
     * 
     * @return type
     */
    public static function getModules()
    {
        if (count(self::$_modules) < 1) {
            return null;
        } else {
            return self::$_modules;
        }
    }

    /**
     * 
     * @return null
     */
    public static function getModuleNames()
    {
        if (count(self::$_modules) < 1) {
            return null;
        } else {
            $moduleNames = array();

            foreach (self::$_modules as $module) {
                $moduleNames[] = $module->getModuleName();
            }

            return $moduleNames;
        }
    }

    /**
     * Error handler
     * 
     * @param type $number
     * @param type $text
     * @param type $file
     * @param type $row
     */
    public static function _errorHandler($number, $text, $file, $row)
    {
        switch ($number) {
            case E_WARNING: case E_USER_WARNING :
                $type = 'Warning';
                break;
            case E_NOTICE: case E_USER_NOTICE:
                $type = 'Notice';
                break;
            default:
                $type = 'Error';
                break;
        }

        $file = basename($file);
        $time = '[' . strftime('%Y-%m-%d %H:%M:%S', time()) . ']';
        $message = "{$time} ~ {$type} ~ {$file} ~ {$row} ~ {$text}" . PHP_EOL;

        if (file_exists(self::$_errorLog) && filesize(self::$_errorLog) > 10000000) {
            file_put_contents(self::$_errorLog, $message);
        } else {
            file_put_contents(self::$_errorLog, $message, FILE_APPEND);
        }
    }

    /**
     * Exception handler
     * 
     * @param Exception $exception
     */
    public static function _exceptionHandler(\Exception $exception)
    {
        $type = get_class($exception);
        $file = $exception->getFile();
        $row = $exception->getLine();
        $text = $exception->getMessage();
        $time = '[' . strftime('%Y-%m-%d %H:%M:%S', time()) . ']';

        $message = "{$time} ~ Uncaught exception: {$type} ~ {$file} ~ {$row} ~ {$text}" . PHP_EOL;

        if (file_exists(self::$_errorLog) && filesize(self::$_errorLog) > 10000000) {
            file_put_contents(self::$_errorLog, $message);
        } else {
            file_put_contents(self::$_errorLog, $message, FILE_APPEND);
        }
    }

    /**
     * 
     */
    public static function run()
    {
        try {
            // configuration

            $configuration = new \THCFrame\Configuration\Configuration(
                    array('type' => 'ini', 'options' => array('env' => ENV))
            );
            Registry::set('configuration', $configuration->initialize());

            // observer events from config

            $events = \THCFrame\Events\Events::getInstance();
            Registry::set('events', $events->initialize());

            // database

            $database = new \THCFrame\Database\Database();
            Registry::set('database', $database->initialize());

            // cache

            $cache = new \THCFrame\Cache\Cache();
            Registry::set('cache', $cache->initialize());

            // session

            $session = new \THCFrame\Session\Session();
            Registry::set('session', $session->initialize());

            // security

            $security = new \THCFrame\Security\Security();
            Registry::set('security', $security->initialize());

            // router

            $router = new \THCFrame\Router\Router(array(
                'url' => urldecode($_SERVER['REQUEST_URI'])
            ));
            Registry::set('router', $router);

            //dispatcher

            $dispatcher = new \THCFrame\Router\Dispatcher();
            Registry::set('dispatcher', $dispatcher->initialize());

            $dispatcher->dispatch($router->getLastRoute());
            // unset globals

            unset($configuration);
            unset($events);
            unset($database);
            unset($cache);
            unset($session);
            unset($security);
            unset($router);
            unset($dispatcher);
        } catch (\Exception $e) {

            $exceptions = array(
                '401' => array(
                    'THCFrame\Security\Exception\Role',
                    'THCFrame\Security\Exception\Unauthorized',
                    'THCFrame\Security\Exception\UserExpired',
                    'THCFrame\Security\Exception\UserInactive',
                    'THCFrame\Security\Exception\UserPassExpired'
                ),
                '404' => array(
                    'THCFrame\Router\Exception\Module',
                    'THCFrame\Router\Exception\Action',
                    'THCFrame\Router\Exception\Controller'
                ),
                '500' => array(
                    'THCFrame\Cache\Exception',
                    'THCFrame\Cache\Exception\Argument',
                    'THCFrame\Cache\Exception\Implementation',
                    'THCFrame\Configuration\Exception',
                    'THCFrame\Configuration\Exception\Argument',
                    'THCFrame\Configuration\Exception\Implementation',
                    'THCFrame\Configuration\Exception\Syntax',
                    'THCFrame\Controller\Exception',
                    'THCFrame\Controller\Exception\Argument',
                    'THCFrame\Controller\Exception\Implementation',
                    'THCFrame\Core\Exception',
                    'THCFrame\Core\Exception\Argument',
                    'THCFrame\Core\Exception\Implementation',
                    'THCFrame\Core\Exception\Property',
                    'THCFrame\Core\Exception\ReadOnly',
                    'THCFrame\Core\Exception\WriteOnly',
                    'THCFrame\Database\Exception',
                    'THCFrame\Database\Exception\Argument',
                    'THCFrame\Database\Exception\Implementation',
                    'THCFrame\Database\Exception\Sql',
                    'THCFrame\Model\Exception',
                    'THCFrame\Model\Exception\Argument',
                    'THCFrame\Model\Exception\Connector',
                    'THCFrame\Model\Exception\Implementation',
                    'THCFrame\Model\Exception\Primary',
                    'THCFrame\Model\Exception\Type',
                    'THCFrame\Model\Exception\Validation',
                    'THCFrame\Module\Exception\Multiload',
                    'THCFrame\Module\Exception\Implementation',
                    'THCFrame\Module\Exception',
                    'THCFrame\Request\Exception',
                    'THCFrame\Request\Exception\Argument',
                    'THCFrame\Request\Exception\Implementation',
                    'THCFrame\Request\Exception\Response',
                    'THCFrame\Router\Exception',
                    'THCFrame\Router\Exception\Argument',
                    'THCFrame\Router\Exception\Implementation',
                    'THCFrame\Security\Exception',
                    'THCFrame\Security\Exception\Implementation',
                    'THCFrame\Security\Exception\HashAlgorithm',
                    'THCFrame\Session\Exception',
                    'THCFrame\Session\Exception\Argument',
                    'THCFrame\Session\Exception\Implementation',
                    'THCFrame\Template\Exception',
                    'THCFrame\Template\Exception\Argument',
                    'THCFrame\Template\Exception\Implementation',
                    'THCFrame\Template\Exception\Parser',
                    'THCFrame\View\Exception',
                    'THCFrame\View\Exception\Argument',
                    'THCFrame\View\Exception\Data',
                    'THCFrame\View\Exception\Implementation',
                    'THCFrame\View\Exception\Renderer',
                    'THCFrame\View\Exception\Syntax'
                ),
                '503' => array(
                    'THCFrame\Database\Exception\Service',
                    'THCFrame\Cache\Exception\Service',
                )
            );

            $exception = get_class($e);

            // attempt to find the approapriate error template, and render

            foreach ($exceptions as $template => $classes) {
                foreach ($classes as $class) {
                    if ($class == $exception) {
                        header('Content-type: text/html');
                        include(APP_PATH . "/modules/app/views/errors/{$template}.phtml");
                        exit();
                    }
                }
            }

            // render fallback template

            header('Content-type: text/html');
            echo 'An error occurred.';
            print_r($e);
            exit();
        }
    }

}
