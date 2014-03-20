<?php

use THCFrame\Model\Model as Model;

/**
 * Description of PhotoModel
 *
 * @author Tomy
 */
class App_Model_Photo extends Model {

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
    protected $_galleryId;

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
     * @validate alphanumeric, max(100)
     * @label title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate alphanumeric, max(50)
     * @label photo name
     */
    protected $_photoName;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate required, max(150)
     * @label thum path
     */
    protected $_pathSmall;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 150
     * 
     * @validate required, max(150)
     * @label origin photo path
     */
    protected $_pathLarge;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 32
     * 
     * @validate required, max(32)
     * @label mime type
     */
    protected $_mime;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, max(8)
     * @label size
     */
    protected $_size;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, max(8)
     * @label width
     */
    protected $_width;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required, max(8)
     * @label height
     */
    protected $_height;

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