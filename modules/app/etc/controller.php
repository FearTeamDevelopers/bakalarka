<?php

namespace App\Etc;

use THCFrame\Events\Events as Events;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Controller\Controller as BaseController;

/**
 * Description of Controller
 *
 * @author Tomy
 */
class Controller extends BaseController
{

    protected static $_imageExtensions = array('gif', 'jpg', 'png', 'jpeg');

    /**
     * @protected
     */
    public function _secured()
    {
        $session = Registry::get("session");
        $view = $this->getActionView();

        $user = $session->get('user');

        if ($user && $user instanceof \App_Model_User) {
            $isLogged = true;

            $userId = $user->getId();
            $userD = \App_Model_User::first(array("id = ?" => $userId));
            $deleted = $userD->getDeleted();

            if ($deleted) {
                $isLogged = false;
            }
        } else {
            $isLogged = false;
        }

        $view->set('islogged', $isLogged);
    }

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $database = Registry::get("database");
        $database->connect();

        // schedule disconnect from database 
        Events::add("framework.controller.destruct.after", function($name) {
            $database = Registry::get("database");
            $database->disconnect();
        });
    }

    /**
     * load user from security context
     */
    public function getUser()
    {
        $security = Registry::get("security");
        $user = $security->getUser();

        return $user;
    }

    /**
     * 
     */
    public function render()
    {

        parent::render();
    }

    protected function loadConfigDb($key)
    {
        $object = \App_Model_Options::first(array('nazev = ?' => $key));
        return $object;
    }

}
