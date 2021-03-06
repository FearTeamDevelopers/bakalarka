<?php

use THCFrame\Model\Model;

/**
 * Description of App_Model_Konverzace
 *
 * @author Tomy
 */
class App_Model_Konverzace extends Model
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
     */
    protected $_fromUser;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_toUser;

    /**
     * @column
     * @readwrite
     * @lenght 256
     * @type text
     * @label message
     * 
     * @validate required, alphanumeric
     */
    protected $_message;

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
