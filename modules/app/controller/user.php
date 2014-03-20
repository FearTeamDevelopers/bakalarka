<?php

use App\Libraries\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of UserController
 *
 * @author Tomy
 */
class App_Controller_User extends Controller {

    /**
     * 
     * @param type $name
     * @param type $username
     * @return string
     * @throws \Exception
     */
    private function _upload($name, $user) {

        if (isset($_FILES[$name]) && !empty($_FILES[$name]["name"])) {
            $file = $_FILES[$name];
            $path = "/public/uploads/team/";

            $size = filesize($file["tmp_name"]);
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = $user->getEmail() . "_" . $user->getId() . "." . $extension;

            if ($size > 5000000) {
                throw new \Exception("Image size exceeds the maximum size limit");
            } elseif (!in_array($extension, self::$_imageExtensions)) {
                throw new \Exception("Images can only be with jpg, jpeg, png or gif extension");
            } elseif (file_exists("." . $path . $filename)) {
                unlink("." . $path . $filename);

                if (move_uploaded_file($file["tmp_name"], "." . $path . $filename)) {
                    return $path . $filename;
                } else {
                    throw new \Exception("An error occured while uploading the photo");
                }
            } else {
                if (move_uploaded_file($file["tmp_name"], "." . $path . $filename)) {
                    return $path . $filename;
                } else {
                    throw new \Exception("An error occured while uploading the photo");
                }
            }
        } else {
            return "";
        }
    }

    /**
     * 
     */
    public function login() {


        if (RequestMethods::post("continue")) {
            $view = $this->getActionView();
            $security = Registry::get("security");
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
                
                $session->set("user",$user) ;
                
            $queue = new App_Model_Queue(array(
                "idAdmin" => 1,
                "idUser" =>$user->getId(),
                "created" =>date("Y-m-d H:i:s"),
                "modified" =>date("Y-m-d H:i:s")
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
    public function logout() {
        $security = Registry::get("security");
        $security->logout();
       
        self::redirect("/login");
    }

    /**
     * 
     */
    public function register() {
        
    }

    /**
     * @before _secured
     */
    public function edit() {
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
   private function authenticate($loginCredential)
    {
$security = Registry::get("security");
        $user = \App_Model_User::first(array(
                    "email = ?" => $loginCredential,
        ));
        
       
                    $security->_authenticated = true;
                    $security->setUser($user);

                  
                        Events::fire("framework.security.authenticate.success", array($user));
                       
     
    }
}
