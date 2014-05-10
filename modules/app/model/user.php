<?php

use THCFrame\Model\Model;
use THCFrame\Security\UserInterface;

/**
 * Description of App_Model_User
 *
 * @author Tomy
 */
class App_Model_User extends Model implements UserInterface
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
     * @type boolean
     * @index
     */
    protected $_active;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 130
     * @index
     *
     * @validate max(130)
     * @label password
     */
    protected $_password;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 25
     * 
     * @validate required, alpha, max(25)
     * @label user role
     */
    protected $_role;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alpha, min(3), max(40)
     * @label first name
     */
    protected $_firstname;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 40
     *
     * @validate required, alpha, min(3), max(40)
     * @label last name
     */
    protected $_lastname;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_lastActive;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_deleted;

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
            $this->setActive(true);
            $this->setDeleted(false);
        }
        $this->setModified(date("Y-m-d H:i:s"));
    }

    /**
     * 
     */
    public function isActive()
    {
        return (boolean) $this->_active;
    }

    /**
     * 
     * @return type
     */
    public function getWholeName()
    {
        return $this->_firstname . " " . $this->_lastname;
    }

    /**
     * 
     * @return type
     */
    public function __toString()
    {
        $str = "Id: {$this->_id} <br/>Name: {$this->_firstname} {$this->_lastname}";
        return $str;
    }

}
