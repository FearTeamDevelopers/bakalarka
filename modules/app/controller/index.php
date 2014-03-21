<?php

use App\Libraries\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
use THCFrame\Request\RequestMethods;
/**
 * Description of IndexController
 *
 * @author Tomy
 */
class App_Controller_Index extends Controller {

    /**
     * @before _secured
     */
    public function index() {
        $view = $this->getActionView();
        $session =  Registry::get("session");
        $user = $session->get("user");
        $userId = $user->getId();
        $queue = App_Model_Queue::all();
        $view->set('queue', $queue);
        
       if (RequestMethods::post('chatTextSubmit')) {

           /**********
            * nechapu proc mi to tady nefunguje :(, v index.phtml mam
            * text input, kterej chci zkontrolovat jestli je prazdnej, ale
            * post mi ho nebere, dunno why
            * *************/
            $message = RequestMethods::post('chatTextInput');
            
            if(empty($message)){
                $errors['chatErrors'] = array("nemůžete odeslat prázdnou zprávu");
                $view->set('errors',$errors);
            }else{
                $konverzace = new App_Model_Konverzace(array(
                   "from" =>$userId,
                    "to" => 1,
                    "message" =>$message,
                    "created" => date("Y-m-d H:i:s"),
                    "modified" => date("Y-m-d H:i:s")
                ));
                $konverzace->save();
                
               $query = App_Model_Konverzace::getQuery(array("tb_konverzace.*"));
                
               $query->join("tb_user", "tb_konverzace.from = k.id", "k",array("k.firstname", "k.lastname"))
                       ->where("tb_konverzace.from = ? ", $userId)
                       ->order("tb_konverzace.created", "desc");
               $vypiskonverzace = App_Model_Konverzace::initialize($query);
                $view->set('vypiskonverzace', $vypiskonverzace);
            }
        }
    }

}
