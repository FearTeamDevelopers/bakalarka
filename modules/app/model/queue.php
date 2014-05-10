<?php

use THCFrame\Model\Model;

/**
 * Description of App_Model_Queue
 *
 * @author Tomy
 */
class App_Model_Queue extends Model
{

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
     * @validate max(8)
     */
    protected $_idUser;

    /**
     * @column
     * @readwrite
     * @type boolean
     */
    protected $_active;

     /**
     * @column
     * @readwrite
     * @type boolean
     */
    protected $_isUserWriting;
     /**
     * @column
     * @readwrite
     * @type boolean
     */
    protected $_isAdminWriting;
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
    public function preSave()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary["raw"];

        if (empty($this->$raw)) {
            $this->setCreated(date("Y-m-d H:i:s"));
        }
        $this->setModified(date("Y-m-d H:i:s"));
    }

}
