<?php

use App\Etc\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class App_Controller_Index extends Controller
{

    public function index()
    {
        $view = $this->getActionView();
        $layout = $this->getLayoutView();

        $session = Registry::get('session');
        $user = $session->get('user');
        $active = $this->loadConfigDb('chatActive');

        if ($user && $user instanceof App_Model_User) {
            $isLogged = true;
        } else {
            $isLogged = false;
        }

        $showBtn = false;
        if ($active->getValue()) {
            $showBtn = true;
        }

        $view->set('islogged', $isLogged);
        $layout->set('showbtn', $showBtn);
    }

    /**
     *  @before _secured
     */
    public function submitChat()
    {
        $view = $this->getActionView();

        $session = Registry::get('session');
        $user = $session->get('user');

        if ($user) {
            $userId = $user->getId();
        } else {
            echo 'Your are not logged in';
        }

        $konverzace = new App_Model_Konverzace(array(
            'fromUser' => $userId,
            'toUser' => 1,
            'message' => RequestMethods::post('chatTextInput')
        ));

        if ($konverzace->validate()) {
            $konverzace->save();
            echo 'success';
        } else {
            echo 'Your message contain disallowed characters';
        }
    }

    /**
     *  @before _secured
     */
    public function loadConversation()
    {
        $this->willRenderLayoutView = false;

        $session = Registry::get('session');
        $user = $session->get('user');

        if ($user) {
            $userId = $user->getId();

            $queue = App_Model_Queue::first(array('idUser = ?' => $userId, 'active = ?' => true));
            if ($queue != null) {

                $query = App_Model_Konverzace::getQuery(array('tb_konverzace.*'));

                $query->join('tb_user', 'tb_konverzace.fromUser = k.id', 'k', array('k.firstname', 'k.lastname'))
                        ->wheresql("tb_konverzace.fromUser = {$userId} OR tb_konverzace.toUser = {$userId}")
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
            } else {
                $queueStart = 1;

                $queueCount = App_Model_Queue::count(
                                array(
                                    'active = ?' => false,
                                    'idUser <> ?' => $userId
                ));
                if ($queueCount == null) {
                    $queueCount = 1;
                }
                $queueCount += $queueStart;
                echo "Jste {$queueCount}. v pořadí, prosíme počkejte pár minut, než na vás dojde řada.";
            }
        }
    }

    /**
     * @before _secured
     */
    public function checkStatus()
    {
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;

        $session = Registry::get('session');
        $user = $session->get('user');

        if ($user) {
            $userId = $user->getId();

            $userObj = App_Model_User::first(array('id = ?' => $userId));
            $q = App_Model_Queue::first(array('active = ?' => true));

            if ($userObj->getDeleted()) {
                $q->delete();
                $session->erase('user');
                echo 2; //user deleted by admin
            } else {
                $queue = App_Model_Queue::first(array('idUser = ?' => $userId, 'active = ?' => true));

                if ($queue != null) {
                    echo 1; //konversation active
                } else {
                    echo 3; //konversation inactive
                }
            }
        } else {
            echo 4; //no user in session - user is not logged
        }
    }

    /**
     * 
     */
    public function checkUser()
    {
        $session = Registry::get('session');
        $user = $session->get('user');

        if ($user && $user instanceof App_Model_User) {
            echo 'ok';
        } else {
            echo 'err';
        }
    }

    public function setWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null) {
            $q->isUserWriting = true;
            $q->save();
        }
    }

    public function setNotWriting()
    {
        $q = App_Model_Queue::first(array('active = ?' => true));
        if ($q != null) {
            $q->isUserWriting = false;
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
