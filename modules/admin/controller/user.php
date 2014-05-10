<?php

use Admin\Etc\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods as RequestMethods;

/**
 * Description of UserController
 *
 * @author Tomy
 */
class Admin_Controller_User extends Controller
{
    /**
     * @before _secured, _admin
     * @param type $name
     * @param type $username
     * @return string
     * @throws \Exception
     */

    /**
     * 
     */
    public function login()
    {
        if (RequestMethods::post('login')) {
            $email = RequestMethods::post('email');
            $password = RequestMethods::post('password');

            $view = $this->getActionView();
            $error = false;

            if (empty($email)) {
                $view->set('email_error', 'Email not provided');
                $error = true;
            }

            if (empty($password)) {
                $view->set('password_error', 'Password not provided');
                $error = true;
            }

            if (!$error) {
                try {
                    $security = Registry::get('security');
                    $status = $security->authenticate($email, $password, true);

                    if ($status) {
                        self::redirect('/admin/');
                    } else {
                        $view->set('account_error', 'Email address and/or password are incorrect');
                    }
                } catch (\Exception $e) {
                    $view->set('account_error', $e->getMessage());
                }
            }
        }
    }

    /**
     * 
     */
    public function logout()
    {
        $security = Registry::get('security');
        $security->logout();
        self::redirect('/admin/');
    }
  
}
