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
     * Ajax
     */
    public function login()
    {
        $this->willRenderLayoutView = false;
        $session = Registry::get('session');

        $user = new App_Model_User(array(
            'firstname' => RequestMethods::post('firstName'),
            'lastname' => RequestMethods::post('lastName'),
            'role' => 'role_user',
        ));

        if ($user->validate()) {
            $id = $user->save();
            $session->set('user', $user);

            $queue = new App_Model_Queue(array(
                'idAdmin' => 1,
                'idUser' => $id,
                'active' => false
            ));

            $queue->save();
            echo 'success';
        } else {
            echo 'Ve jmene nebo prijmeni jsou nepovolene znaky';
        }
    }

    /**
     * Ajax
     */
    public function logoutA()
    {
        $session = Registry::get('session');
        $user = $session->get('user');
        $userId = $user->getId();

        $queue = App_Model_Queue::first(array(
                    'idUser = ?' => $userId
        ));
        $queue->delete();
        $session->erase('user');

        echo 'Admin ukoncil konverzaci';
    }

    /**
     * 
     */
    public function logout()
    {
        $session = Registry::get('session');
        $user = $session->get('user');
        $userId = $user->getId();

        $queue = App_Model_Queue::first(array(
                    'idUser = ?' => $userId
        ));
        $queue->delete();
        $session->erase('user');
        $userD = App_Model_User::first(array('id = ?' => $userId));
        $userD->deleted = true;
        $userD->save();

        self::redirect('/');
    }

}
