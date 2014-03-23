<?php

use App\Etc\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of UserController
 *
 * @author Tomy
 */
class App_Controller_User extends Controller
{


    /**
     * 
     */
    public function login()
    {
        if (RequestMethods::post("continue")) {
            $view = $this->getActionView();
            $session = Registry::get("session");
            $errors = array();

            $email = App_Model_User::first(array('email = ?' => RequestMethods::post("email")), array('email'));

            if ($email) {
                $errors["email"] = array("Tento email, zrovna někdo používá");
            }

            $user = new App_Model_User(array(
                "firstname" => RequestMethods::post("firstname"),
                "lastname" => RequestMethods::post("lastname"),
                "email" => RequestMethods::post("email"),
                "role" => "role_user",
                "created" => date("Y-m-d H:i:s")
            ));
            if (empty($errors) && $user->validate()) {
                $user->save();
                $session->set("user", $user);

                $queue = new App_Model_Queue(array(
                    "idAdmin" => 1,
                    "idUser" => $user->getId(),
                    "created" => date("Y-m-d H:i:s"),
                    "modified" => date("Y-m-d H:i:s"),
                    'active' => false
                ));

                $queue->save();
                self::redirect("/");
            } else {
                $view->set("errors", $errors + $user->getErrors());
            }
        }
    }

    /**
     * 
     */
    public function logout()
    {
        $session = Registry::get('session');
        $user=$session->get('user');
        $userId = $user->getId();
        
        
        $bla= App_Model_Queue::first(array(
            "idUser = ?" =>$userId
        ));
        $bla->delete();
        
        $blabla=  App_Model_User::first(array(
            "id = ?" => $userId
        ));
        $blabla->delete();
        $session->erase('user');

        self::redirect("/login");
    }

    /**
     * @before _secured
     */
    public function edit()
    {
        $view = $this->getActionView();
        $userId = $this->getUser()->getId();
        $errors = array();

        //required to activate database connection
        $user = App_Model_User::first(array(
                    "id = ?" => $userId
        ));

        if (RequestMethods::post("editProfile")) {
            $security = Registry::get("security");

            $passComparation = $security->comparePasswords(
                    RequestMethods::post("password"), RequestMethods::post("password2")
            );

            if (!$passComparation) {
                $errors["password2"] = array("Paswords doesnt match");
            }

            if (RequestMethods::post("email") != $user->email) {
                $email = App_Model_User::first(array('email = ?' => RequestMethods::post("email", $user->email)), array('email'));
                if ($email) {
                    $errors["email"] = array("Email is already used");
                }
            }

            $pass = RequestMethods::post("password");
            if ($pass == "") {
                $hash = $user->password;
            } else {
                $hash = $security->getHash($pass);
            }

            $user->firstname = RequestMethods::post("firstname");
            $user->lastname = RequestMethods::post("lastname");
            $user->email = RequestMethods::post("email");
            $user->password = $hash;
            $user->dob = date("Y-m-d", strtotime(RequestMethods::post("dob")));
            $user->cfbuPersonalNum = RequestMethods::post("cfbuPersonalNum");
            $user->playerNum = RequestMethods::post("playerNum");
            $user->team = RequestMethods::post("team");
            $user->nickname = RequestMethods::post("nickname");
            $user->position = RequestMethods::post("position");
            $user->grip = RequestMethods::post("grip");
            $user->other = RequestMethods::post("other");

            if (empty($errors) && $user->validate()) {
                try {
                    $path = $this->_upload("photo", $user);

                    if ($path != "") {
                        $user->setPhoto($path);
                    }
                } catch (\Exception $e) {
                    $errors["photo"] = array($e->getMessage());
                }

                if (empty($errors)) {
                    $user->save();
                    $security->setUser($user);

                    $view->flashMessage("All changes were successfully saved");
                    self::redirect("/");
                } else {
                    $view->set("errors", $errors + $user->getErrors());
                }
            }

            $view->set("errors", $errors + $user->getErrors());
        }

        $view->set("user", $user);
    }

}
