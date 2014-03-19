<?php

use THCFrame\Model\Model;
use THCFrame\Security\UserInterface;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_Konverzace extends Model implements UserInterface {

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
     * @lenght 150
     * @type text
     * @label message
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
