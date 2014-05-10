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
            'pattern' => '/logoutA',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'logoutA',
        ),
        array(
            'pattern' => '/submitChat',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'submitChat',
        ),
        array(
            'pattern' => '/loadConversation',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'loadConversation',
        ),
        array(
            'pattern' => '/checkStatus',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'checkStatus',
        ),
        array(
            'pattern' => '/checkUser',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'checkUser',
        ),
        array(
            'pattern' => '/setWriting',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'setWriting',
        ),
        array(
            'pattern' => '/setNotWriting',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'setNotWriting',
        ),
        array(
            'pattern' => '/userIsWriting',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'userIsWriting',
        ),
        array(
            'pattern' => '/adminIsWriting',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'adminIsWriting',
        )
    );
}