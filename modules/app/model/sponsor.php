<?php

use THCFrame\Model\Model as Model;

/**
 * Description of SponsorModel
 *
 * @author Tomy
 */
class App_Model_Sponsor extends Model {

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
     * @type boolean
     * @index
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, alphanumeric, max(60)
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate required, url, max(60)
     */
    protected $_url;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate required, max(100)
     */
    protected $_logo;

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

}