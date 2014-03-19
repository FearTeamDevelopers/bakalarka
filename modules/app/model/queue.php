<?php

use THCFrame\Model\Model;
use THCFrame\Security\UserInterface;

/**
 * Description of UserModel
 *
 * @author Tomy
 */
class App_Model_Queue extends Model implements UserInterface {

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
     * lenght 11
     */
    protected $_idAdmin;

    /**
     * @column
     * @readwrite
     * @type integer
     * lenght 11

     */
    protected $_idUser;

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
public function isActive() {
    }
}

