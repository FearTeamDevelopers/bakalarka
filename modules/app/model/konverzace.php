<?php

use THCFrame\Model\Model;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_Konverzace extends Model {

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
    protected $_from;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_to;

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

}
