<?php

namespace THCFrame\Configuration\Driver;

use THCFrame\Registry\Registry as Registry;
use THCFrame\Core\ArrayMethods as ArrayMethods;
use THCFrame\Configuration as Configuration;
use THCFrame\Configuration\Exception as Exception;

/**
 * Description of Ini
 *
 * @author Tomy
 */
class Ini extends Configuration\Driver
{

    private $_defaultConfig;

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->parseDefault('./vendors/thcframe/configuration/default/defaultConfig.ini');

        switch ($this->getEnv()) {
            case 'dev': {
                    $this->parse('./application/configuration/config_dev.ini');
                    break;
                }
            case 'qa': {
                    $this->parse('./application/configuration/config_qa.ini');
                    break;
                }
            case 'live': {
                    $this->parse('./application/configuration/config_live.ini');
                    break;
                }
        }
    }

    /**
     * 
     * @return type
     */
    private function mergeConfiguration()
    {
        $merged = $this->_defaultConfig;

        foreach ($this->_parsed as $key => $value) {
            $merged[$key] = array_merge($this->_defaultConfig[$key], $this->_parsed[$key]);
        }

        return $merged;
    }

    /**
     * 
     * @param type $path
     */
    protected function parseDefault($path)
    {
        if (empty($path)) {
            throw new Exception\Argument('Path argument is not valid');
        }

        if (!isset($this->_defaultConfig)) {
            $config = array();

            ob_start();
            include($path);
            $string = ob_get_contents();
            ob_end_clean();

            $pairs = parse_ini_string($string);

            if ($pairs == false) {
                throw new Exception\Syntax('Could not parse configuration file');
            }

            foreach ($pairs as $key => $value) {
                $config = $this->_pair($config, $key, $value);
            }

            $this->_defaultConfig = $config;
        }
    }

    /**
     * 
     * @param type $config
     * @param type $key
     * @param type $value
     * @return type
     */
    protected function _pair($config, $key, $value)
    {
        if (strstr($key, '.')) {
            $parts = explode('.', $key, 2);

            if (empty($config[$parts[0]])) {
                $config[$parts[0]] = array();
            }

            $config[$parts[0]] = $this->_pair($config[$parts[0]], $parts[1], $value);
        } else {
            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * 
     * @param type $path
     * @return type
     * @throws Exception\Argument
     * @throws Exception\Syntax
     */
    public function parse($path)
    {
        if (empty($path)) {
            throw new Exception\Argument('Path argument is not valid');
        }

        if (!isset($this->_parsed)) {
            $config = array();

            ob_start();
            include($path);
            $string = ob_get_contents();
            ob_end_clean();

            $pairs = parse_ini_string($string);

            if ($pairs == false) {
                throw new Exception\Syntax('Could not parse configuration file');
            }

            foreach ($pairs as $key => $value) {
                $config = $this->_pair($config, $key, $value);
            }

            $this->_parsed = $config;
        }

        $merged = $this->mergeConfiguration();
        $configObject = ArrayMethods::toObject($merged);

        Registry::set('config', $configObject);
        Registry::set('dateformat', $configObject->system->default->dateformat);

        return $configObject;
    }

}
