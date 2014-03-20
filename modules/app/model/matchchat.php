<?php

use THCFrame\Model\Model as Model;

/**
 * Description of MatchChatModel
 *
 * @author Tomy
 */
class App_Model_MatchChat extends Model {

    /**
     * @column
     * @readwrite
     * @primary
     * @type auto_increment
     */
    protected $_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, numeric, max(8)
     */
    protected $_matchId;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate numeric, max(8)
     * @label reply
     */
    protected $_reply;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 85
     * 
     * @validate alphanumeric, max(85)
     * @label author
     */
    protected $_author;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate alphanumeric, max(100)
     * @label title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 256
     * 
     * @validate required, max(2048)
     * @label text
     */
    protected $_body;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_created;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_modified;

    /**
     * 
     */
    public function preSave() {
        $primary = $this->getPrimaryColumn();
        $raw = $primary["raw"];

        if (empty($this->$raw)) {
            $this->setCreated(date("Y-m-d H:i:s"));
            $this->setActive(true);
        }
        $this->setModified(date("Y-m-d H:i:s"));
    }

    /**
     * 
     * @return type
     */
    public function getReplies() {
        return self::all(
                        array(
                    "reply = ?" => $this->getId(),
                    "active = ?" => true,
                        ), array("*"), "created", "desc");
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public static function fetchReplies($id) {
        $message = new self(array(
            "id" => $id
        ));

        return $message->getReplies();
    }

}