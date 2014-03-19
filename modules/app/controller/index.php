<?php

use App\Libraries\Controller as Controller;
use THCFrame\Registry\Registry as Registry;
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

        $queue = App_Model_Queue::all();
        $view->set('queue', $queue);
    }

}
