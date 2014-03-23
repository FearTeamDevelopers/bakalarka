<?php

use THCFrame\Module\Module as Module;

/**
 * Description of Module
 *
 * @author Tomy
 */
class Admin_Etc_Module extends Module {

    /**
     * @read
     */
    protected $_moduleName = "Admin";
    protected $_routes = array(
        array(
            'pattern' => '/admin/login',
            'module' => 'admin',
            'controller' => 'user',
            'action' => 'login',
        ),
        array(
            'pattern' => '/admin/logout',
            'module' => 'admin',
            'controller' => 'user',
            'action' => 'logout',
        ),
        array(
            'pattern' => '/admin/submitChat',
            'module' => 'admin',
            'controller' => 'index',
            'action' => 'submitChat',
        ),
        array(
            'pattern' => '/admin/delete',
            'module' => 'admin',
            'controller' => 'user',
            'action' => 'delete',
        )
    );

}
