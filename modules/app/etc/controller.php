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
class Controller extends BaseController {

    protected static $_imageExtensions = array('gif', 'jpg', 'png', 'jpeg');
    
    /**
     * @protected
     */
    public function _secured() {
        $session = Registry::get("session");

        $user = $session->get('user');
        if (!$user) {
            self::redirect("/login");
        }
    
    }

    /**
     * @protected
     */
    public function _admin() {
        $security = Registry::get("security");
        $view = $this->getActionView();

        if ($security->getUser() && !$security->isGranted("role_admin")) {
            $view->flashMessage("Access denied! Administrator access level required.");
            $security->logout();
            self::redirect("/login");
        }
    }

    /**
     * 
     * @param type $options
     */
    public function __construct($options = array()) {
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
    public function getUser() {
        $security = Registry::get("security");
        $user = $security->getUser();

        return $user;
    }

    /**
     * 
     */
    public function render() {
        if ($this->getUser()) {
            if ($this->getActionView()) {
                $this->getActionView()
                        ->set("authUser", $this->getUser());
            }

            if ($this->getLayoutView()) {
                $this->getLayoutView()
                        ->set("authUser", $this->getUser());
            }
        }

        parent::render();
    }

}