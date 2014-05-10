<?php

use Admin\Etc\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Registry\Registry;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class Admin_Controller_Index extends Controller
{

    /**
     * @before _secured, _admin
     */
    public function index()
    {
        $view = $this->getActionView();
        $query3 = App_Model_Queue::getQuery(array('tb_queue.*'));

        $query3->join('tb_user', 'tb_queue.idUser = u.id', 'u', array('u.firstname', 'u.lastname'))
                ->wheresql('tb_queue.active = true and  u.deleted = false');
        $user = App_Model_Queue::initialize($query3);
        $view->set('user', $user);

        $qCount = App_Model_User::count(array(
                    'deleted = ?' => false,
                    'role = ?' => 'role_user'
        ));
        $view->set('qcount', $qCount);

        /*         * ****************************
         * 
         * dotaz na vypisovani uzivatelu v Q
         * 
         * ************************************ */
        $query = App_Model_Queue::getQuery(array('tb_queue.*'));

        $query->join('tb_user', 'tb_queue.idUser = user.id', 'user', array('user.firstname', 'user.lastname'))
                ->wheresql('user.deleted = false')
                ->order('tb_queue.created', 'asc');

        $qArray = App_Model_Queue::initialize($query);
        $view->set('qarray', $qArray);

        /* -------------------------------------------------------------- */

        /*         * ********************
         * 
         * Dotaz na vypisování konverzace admina s uzivatelem
         * 
         * ******************** */
        $query2 = App_Model_Konverzace::getQuery(array('tb_konverzace.*'));

        $query2->join('tb_user', 'tb_konverzace.fromUser = u.id', 'u', array('u.firstname', 'u.lastname'))
                ->wheresql('tb_konverzace.fromUser = (select idUser from tb_queue where active = true) OR tb_konverzace.toUser = (select idUser from tb_queue where active = true)')
                ->order('tb_konverzace.created', 'asc');

        $vypiskonverzace = App_Model_Konverzace::initialize($query2);
        $view->set('vypiskonverzace', $vypiskonverzace);

        /* ------------------------------------------------------------- */
        /*         * ****************-OPTIONS-******************** */
        $welcomeMessage = $this->loadConfigDb('welcomeMessage');
        $activeChat = $this->loadConfigDb('chatActive');
        $view->set('welcomemessage', $welcomeMessage);
        $view->set('chatActive', $activeChat);
    }

    /*
     * @before _secured, _admin
     * ajaxem volaná metoda
     */

    public function changeStatus($id)
    {
        $this->willRenderActionView = false;
        $this->willRenderLayoutView = false;


        $bla = App_Model_Queue::first(array('active = ?' => true));
        if ($bla != null) {
            $bla->setActive(false);
            $bla->save();
        } else {

            $queStatus = App_Model_Queue::first(array(
                        'id = ?' => $id
            ));
            if ($queStatus->active == false) {
                $userId = $queStatus->getIdUser();

                $konverzace = App_Model_Konverzace::count(array(
                            'toUser = ?' => $userId
                ));

                $message = $this->loadConfigDb('welcomeMessage');

                if ($konverzace == 0 && $message->getValue() != '') {
                    $k = new App_Model_Konverzace(array(
                        'fromUser' => 1,
                        'toUser' => $userId,
                        'message' => $message->getValue()
                    ));
                    $k->save();
                }

                $queStatus->setActive(true);
                $queStatus->save();
            }
        }
        self::redirect('/admin/');
    }

    /**
     * @before _secured, _admin
     */
    public function submitChat()
    {
        $view = $this->getActionView();
        $user = App_Model_Queue::first(array('active = ?' => true));
        $userId = $user->getIdUser();

        $message = RequestMethods::post('chatTextInput');

        $konverzace = new App_Model_Konverzace(array(
            'fromUser' => 1,
            'toUser' => $userId,
            'message' => $message
        ));

        if ($konverzace->validate()) {
            $konverzace->save();
            self::redirect('/admin/');
        } else {
            $view->set('error', $konverzace->getErrors());
        }
    }

    /**
     * @before _secured, _admin
     *
     */
    public function loadChat()
    {
        $this->willRenderLayoutView = false;

        $queue = App_Model_Queue::first(array('active = ?' => true));
        if ($queue != null) {

            $query = App_Model_Konverzace::getQuery(array('tb_konverzace.*'));

            $query->join('tb_user', 'tb_konverzace.fromUser = u.id', 'u', array('u.firstname', 'u.lastname'))
                    ->where("tb_konverzace.fromUser = {$queue->idUser} OR tb_konverzace.toUser = {$queue->idUser}")
                    ->order('tb_konverzace.created', 'asc');

            $vypiskonverzace = App_Model_Konverzace::initialize($query);
            $str = '';

            foreach ($vypiskonverzace as $k) {
                if ($k->fromUser == 1) {
                    $str .= "<div  class=\"messageNameRed\">{$k->firstname} {$k->lastname}</div>";
                } elseif ($k->fromUser != 1) {
                    $str .= "<div  class=\"messageNameBlue\">{$k->firstname} {$k->lastname}</div>";
                }
                $str .= "<div class=\"message\">{$k->message}</div>";
            }
            $str .= "<div id=\"clearence\"></div>";

            echo $str;
        }
    }

    /**
     * @before _secured, _admin
     */
    public function loadQ()
    {

        $view = $this->getActionView();
        $this->willRenderLayoutView = false;
        $qCount = App_Model_User::count(array(
                    'deleted = ?' => false,
                    'role = ?' => 'role_user'));
        $query = App_Model_Queue::getQuery(array('tb_queue.*'));


        $query->join('tb_user', 'tb_queue.idUser = user.id', 'user', array('user.firstname', 'user.lastname'))
                ->where('user.deleted = ?', false)
                ->order('tb_queue.created', 'asc');

        $qArray = App_Model_Queue::initialize($query);
        $view->set('qarray', $qArray);
        $str = '';

        $str .='<div class="countQ">Lidí ve frontě: ' . $qCount . '</div>';
        foreach ($qArray as $bla) {
            $str .= "<div class=\"qUserWrapper\">";
            $str .= "<div class=\"qUserName\">{$bla->firstname} {$bla->lastname}</div>";
            $str .= "<form method=\"post\" action=\"/admin/index/changeStatus/{$bla->id}\" class=\"qButtons\">";
            $str .= '<input type="submit" class="qChangeStatus" value="Active | Deactive"/></form>';
            $str .= '</div>';
        }
        $str .= "<div id=\"clearence\"></div>";

        echo $str;
    }

    /**
     * @before _secured, _admin
     */
    public function deleteUserFromQ()
    {
        $view = $this->getActionView();

        $q = App_Model_Queue::first(array(
                    'active = ?' => true
        ));

        $user = App_Model_User::first(array(
                    'id = ?' => $q->getIdUser()
                        )
        );

        if (NULL === $user) {
            $view->flashMessage('User not found');
            self::redirect('/admin/');
        } else {
            $user->deleted = true;
            $user->save();
            self::redirect('/admin/');
        }
    }

    public function playSound()
    {
        $session = Registry::get("session");
        $qCountBla = $session->get("qcountbla");
        $qCount = App_Model_User::count(array(
                    'deleted = ?' => false,
                    'role = ?' => 'role_user'));


        if ($qCountBla < $qCount) {
            $session->set('qcountbla', $qCount);
            echo 'bla';
        } else {
            $session->set('qcountbla', $qCount);
        }
    }

    public function saveConfigData()
    {
        if (RequestMethods::post('submitWelcomeMessage')) {
            $value = App_Model_Options::first(array('nazev = ?' => 'welcomeMessage'));
            $value2 = App_Model_Options::first(array('nazev = ?' => 'chatActive'));
            $value->value = (RequestMethods::post('welcomeMessage'));
            $value2->value = (RequestMethods::post('activeChat'));

            if ($value->validate()) {
                $value->save();
            }
            if ($value2->validate()) {
                $value2->save();
            }
        }
        self::redirect('/admin/');
    }

    public function clearSession()
    {
        if (ENV == 'dev') {
            $session = Registry::get('session');
            $session->erase('user');
        }
    }

    public function setWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null) {
            $q->isAdminWriting = true;
            $q->save();
        }
    }

    public function setNotWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null) {
            $q->isAdminWriting = false;
            $q->save();
        }
    }

    public function userIsWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null & $q->getIsUserWriting()) {
            echo 1;
        } else {
            echo 2;
        }
    }

    public function adminIsWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null & $q->getIsAdminWriting()) {
            echo 1;
        } else {
            echo 2;
        }
    }

}
