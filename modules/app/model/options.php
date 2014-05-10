<?php

use THCFrame\Model\Model;

/**
 * Description of App_Model_Prepared
 *
 * @author Tomy
 */
class App_Model_Options extends Model
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
     * @lenght 50
     * @type text
     * @label nazev
     * 
     * @validate
     */
    protected $_nazev;

    /**
     * @column
     * @readwrite
     * @lenght 250
     * @type text
     */
    protected $_value;

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
