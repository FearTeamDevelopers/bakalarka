<?php

use Admin\Etc\Controller as Controller;
use THCFrame\Request\RequestMethods;

/**
 * Description of IndexController
 *
 * @author Tomy
 */
class Admin_Controller_Index extends Controller {

    /**
     * @before _secured, _admin
     */
    public function index() {
        $view = $this->getActionView();
        $query3 = App_Model_Queue::getQuery(array("tb_queue.*"));

        $query3->join("tb_user", "tb_queue.idUser = u.id", "u", array("u.firstname", "u.lastname"))
                ->wheresql("tb_queue.active = true");
        $user = App_Model_Queue::initialize($query3);
        $view->set('user', $user);

        $qCount = App_Model_Queue::count();
        $view->set('qcount', $qCount);

        /*         * ****************************
         * 
         * dotaz na vypisovani uzivatelu v Q
         * 
         * ************************************ */
        $query = App_Model_Queue::getQuery(array("tb_queue.*"));

        $query->join("tb_user", "tb_queue.idUser = user.id", "user", array("user.firstname", "user.lastname"))
                ->order("tb_queue.created", "asc");

        $qArray = App_Model_Queue::initialize($query);
        $view->set('qarray', $qArray);

        /* -------------------------------------------------------------- */

        /*         * ********************
         * 
         * Dotaz na vypisování konverzace admina s uzivatelem
         * 
         * ******************** */
        $query2 = App_Model_Konverzace::getQuery(array("tb_konverzace.*"));

        $query2->join("tb_user", "tb_konverzace.from = u.id", "u", array("u.firstname", "u.lastname"))
                ->wheresql("tb_konverzace.from = (select idUser from tb_queue where active = true) OR tb_konverzace.to = (select idUser from tb_queue where active = true)")
                ->order("tb_konverzace.created", "asc");


        $vypiskonverzace = App_Model_Konverzace::initialize($query2);
        $view->set('vypiskonverzace', $vypiskonverzace);
        /* ------------------------------------------------------------- */
    }

    /*
     * @before _secured, _admin
     * ajaxem volaná metoda
     */

    public function changeStatus($id) {
        $this->willRenderActionView = false;
        $this->willRenderLayoutView = false;


        $bla = App_Model_Queue::first(array("active = ?" => true));
        if ($bla != null) {
            $bla->setActive(false);
            $bla->save();
        } else {

            $queStatus = App_Model_Queue::first(array(
                        "id = ?" => $id
            ));
            if ($queStatus->active == false) {
                $queStatus->setActive(true);
                $queStatus->save();
            }
        }
        self::redirect('/admin/');
    }

    /**
     * @before _secured, _admin
     */
    public function submitChat() {
        $view = $this->getActionView();
        $user = App_Model_Queue::first(array("active = ?" => true));
        $userId = $user->getIdUser();

        $message = RequestMethods::post('chatTextInput');

        $konverzace = new App_Model_Konverzace(array(
            "from" => 1,
            "to" => $userId,
            "message" => $message,
            "created" => date("Y-m-d H:i:s"),
            "modified" => date("Y-m-d H:i:s")
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
    public function loadChat() {
        $this->willRenderLayoutView = false;

        $queue = App_Model_Queue::first(array("active = ?" => true));
        if ($queue != null) {

            $query = App_Model_Konverzace::getQuery(array("tb_konverzace.*"));

            $query->join("tb_user", "tb_konverzace.from = u.id", "u", array("u.firstname", "u.lastname"))
                    ->where("tb_konverzace.from = {$queue->idUser} OR tb_konverzace.to = {$queue->idUser}")
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
            echo $str;
        }
    }

    /**
     * @before _secured, _admin
     */
    public function loadQ() {
        $view = $this->getActionView();
        $this->willRenderLayoutView = false;
        $qCount = App_Model_Queue::count();
        $query = App_Model_Queue::getQuery(array("tb_queue.*"));

        $query->join("tb_user", "tb_queue.idUser = user.id", "user", array("user.firstname", "user.lastname"))
                ->order("tb_queue.created", "asc");

        $qArray = App_Model_Queue::initialize($query);
        $view->set('qarray', $qArray);
        $str = '';
        
        $str .="<div class=\"countQ\">Lidí ve frontě: {$qCount}</div>";
        foreach ($qArray as $bla) {
            $str .= "<div class=\"qUserWrapper\">";
            $str.="<div class=\"qUserName\">{$bla->firstname} {$bla->lastname}</div>";
            $str.= "<form method=\"post\" action=\"/admin/index/changeStatus/{$bla->id}\" class=\"qButtons\">";
            $str.= "<input type=\"submit\" class=\"qChangeStatus\" value=\"Active | Deactive\"/></form>";
            $str.= "</div>";
        }

        echo $str;
    }

}
