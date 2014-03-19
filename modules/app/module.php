<?php

use THCFrame\Module\Module as Module;

/**
 * Description of Module
 *
 * @author Tomy
 */
class App_Module extends Module{

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
            'pattern' => '/register/:key',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'register',
            'args' => ':key'
        ),
        array(
            'pattern' => '/team',
            'module' => 'app',
            'controller' => 'team',
            'action' => 'index',
        ),
        array(
            'pattern' => '/profil',
            'module' => 'app',
            'controller' => 'user',
            'action' => 'edit',
        ),
        array(
            'pattern' => '/kecarna',
            'module' => 'app',
            'controller' => 'chat',
            'action' => 'index',
        ),
        array(
            'pattern' => '/treninky',
            'module' => 'app',
            'controller' => 'training',
            'action' => 'index',
        ),
        array(
            'pattern' => '/zapasy',
            'module' => 'app',
            'controller' => 'match',
            'action' => 'index',
        ),
        array(
            'pattern' => '/kontakt',
            'module' => 'app',
            'controller' => 'contact',
            'action' => 'index',
        ),
        array(
            'pattern' => '/novinky/:id',
            'module' => 'app',
            'controller' => 'index',
            'action' => 'newsDetail',
            'args' => ':id'
        ),
        array(
            'pattern' => '/zapasy/detail/:id',
            'module' => 'app',
            'controller' => 'match',
            'action' => 'detail',
            'args' => ':id'
        ),
        array(
            'pattern' => '/trenink/dochazka/:id/:status',
            'module' => 'app',
            'controller' => 'training',
            'action' => 'attend',
            'args' => ':id',
            'args2' => ':status'
        )
        
    );
}