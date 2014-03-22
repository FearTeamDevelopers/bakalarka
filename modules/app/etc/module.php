<?php

use THCFrame\Module\Module as Module;

/**
 * Description of Module
 *
 * @author Tomy
 */
class App_Etc_Module extends Module{

    /**
     * @read
     */
    protected $_moduleName = "App";
        
    protected $_routes = array(
        array(
            'pattern' => '/login',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'login',
        ),
        array(
            'pattern' => '/logout',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'logout',
        ),
        array(
            'pattern' => '/submitChat',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'submitChat',
        )
        
    );
}