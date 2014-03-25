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

    /**
     * @before _secured
     */
    public function index()
    {
        $view = $this->getActionView();
        $session = Registry::get("session");
        $user = $session->get("user");

        if ($user) {
            $userId = $user->getId();

            $queueStatus = App_Model_Queue::first(array('idUser = ?' => $userId, 'active = ?' => true));
            if ($queueStatus) {
                $queueCount = 1;
            } else {
                $queueCount = App_Model_Queue::count(
                                array(
                                    'active = ?' => false,
                                    'idUser <> ?' => $userId
                                )
                );
                if (null === $queueCount) {
                    $queueCount = 1;
                }
            }

            $view->set('queuecount', $queueCount + 1)
                    ->set('questatus', $queueStatus);

            $query = App_Model_Konverzace::getQuery(array("tb_konverzace.*"));

            $query->join("tb_user", "tb_konverzace.from = k.id", "k", array("k.firstname", "k.lastname"))
                    ->wheresql("tb_konverzace.from = {$userId} OR tb_konverzace.to = {$userId}")
                    ->order("tb_konverzace.created", "asc");

            $vypiskonverzace = App_Model_Konverzace::initialize($query);
            $view->set('vypiskonverzace', $vypiskonverzace);
        }
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
            $view->set('error', 'user id is not set');
            self::redirect('/');
        }

        $konverzace = new App_Model_Konverzace(array(
            "from" => $userId,
            "to" => 1,
            "message" => RequestMethods::post('chatTextInput'),
            "created" => date("Y-m-d H:i:s"),
            "modified" => date("Y-m-d H:i:s")
        ));

        if ($konverzace->validate()) {
            $konverzace->save();
            echo 'success';
            //self::redirect('/');
        } else {
            //echo serialize($konverzace->getErrors());
            echo 'error';
        }
    }

    /**
     *  @before _secured
     */
    public function loadKonversation()
    {
        $this->willRenderLayoutView = false;

        $session = Registry::get("session");
        $user = $session->get("user");

        if ($user) {
            $userId = $user->getId();

            $queue = App_Model_Queue::first(array("idUser = ?" => $userId, "active = ?" => true));
            if ($queue != null) {

                $query = App_Model_Konverzace::getQuery(array("tb_konverzace.*"));

                $query->join("tb_user", "tb_konverzace.from = k.id", "k", array("k.firstname", "k.lastname"))
                        ->wheresql("tb_konverzace.from = {$userId} OR tb_konverzace.to = {$userId}")
                        ->order("tb_konverzace.created", "asc");

                $vypiskonverzace = App_Model_Konverzace::initialize($query);
                $str = '';

                foreach ($vypiskonverzace as $k) {
                    if ($k->from == 1) {
                        $str .= "<div  class=\"messageNameRed\">{$k->firstname} {$k->lastname}</div>";
                    } elseif ($k->from != 1) {
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

        $session = Registry::get("session");
        $user = $session->get("user");

        if ($user) {
            $userId = $user->getId();

            $userObj = App_Model_User::first(array('id = ?' => $userId));

            if ($userObj->getDeleted()) {
                echo 2; //user deleted by admin
            } else {
                $queue = App_Model_Queue::first(array("idUser = ?" => $userId, "active = ?" => true));

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

}
