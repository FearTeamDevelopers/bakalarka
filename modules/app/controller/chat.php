<?php

use App\Libraries\Controller as Controller;
use THCFrame\Registry\Registry;
use THCFrame\Request\RequestMethods;

/**
 * Description of ChatController
 *
 * @author Tomy
 */
class App_Controller_Chat extends Controller {

    /**
     * @before _secured
     */
    public function index() {
        $view = $this->getActionView();

        $messages = App_Model_Chat::all(array(
                    "active = ?" => true,
                    "reply = ?" => 0
                        ), array("*"), "created", "asc", 10);

        if (RequestMethods::post("sendMessage")) {
            $user = $this->getUser();

            $chat = new App_Model_Chat(array(
                "author" => $user->getWholeName(),
                "title" => RequestMethods::post("title"),
                "body" => RequestMethods::post("body"),
                "reply" => RequestMethods::post("reply")
            ));

            if ($chat->validate()) {
                $chat->save();
                self::redirect("/kecarna");
            }
            $view->set("errors", $chat->getErrors());
        }

        $view->set("messages", $messages);
    }

}